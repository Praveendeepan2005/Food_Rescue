<?php
require 'backend/config/db.php';
$c = getDBConnection();
$s = oci_parse($c, "select f.alert_id, f.assigned_vol_id, f.latitude as f_lat, f.longitude as f_lng, f.ngo_lat as f_ngo_lat, f.ngo_lng as f_ngo_lng, d.latitude as d_lat, d.longitude as d_lng, n.latitude as nm_lat, n.longitude as nm_lng from food_alerts f left join users d on f.donor_id=d.user_id left join users n on f.assigned_ngo_id=n.user_id where f.alert_id=21");
oci_execute($s);
print_r(oci_fetch_assoc($s));
$v = oci_parse($c, "select user_id, latitude, longitude from users where role='VOLUNTEER' and rownum=1");
oci_execute($v);
print_r(oci_fetch_assoc($v));
