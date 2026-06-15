<?php
require_once __DIR__ . '/backend/config/db.php';

$conn = getDBConnection();

$queries = [
    // 1. Create Orphanages Table
    "CREATE TABLE orphanages (
        orphanage_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
        ngo_id NUMBER NOT NULL,
        orphanage_name VARCHAR2(100) NOT NULL,
        contact_person VARCHAR2(100),
        phone_number VARCHAR2(20),
        address VARCHAR2(255),
        city VARCHAR2(100),
        latitude NUMBER(10, 8),
        longitude NUMBER(11, 8),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // 2. Create Deliveries Table
    "CREATE TABLE deliveries (
        delivery_id NUMBER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
        donation_id NUMBER NOT NULL,
        ngo_id NUMBER NOT NULL,
        volunteer_id NUMBER,
        orphanage_id NUMBER NOT NULL,
        pickup_address VARCHAR2(255),
        delivery_address VARCHAR2(255),
        status VARCHAR2(20) DEFAULT 'ASSIGNED',
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP
    )",
    
    // Add indices for performance
    "CREATE INDEX idx_orphanages_ngo ON orphanages(ngo_id)",
    "CREATE INDEX idx_deliveries_vol ON deliveries(volunteer_id)",
    "CREATE INDEX idx_deliveries_ngo ON deliveries(ngo_id)"
];

echo "Starting Schema Update...\n";

foreach ($queries as $sql) {
    $stmt = oci_parse($conn, $sql);
    $result = @oci_execute($stmt);
    
    if (!$result) {
        $e = oci_error($stmt);
        echo "Error executing query: " . $e['message'] . "\n";
    } else {
        echo "Success: " . substr($sql, 0, 50) . "...\n";
    }
}

echo "Schema Update Finished.\n";
