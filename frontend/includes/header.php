<?php
/**
 * header.php  — Shared <head> include
 * Requires $pageTitle to be set before inclusion.
 */
$pageTitle = $pageTitle ?? 'Food Link | Serve. Link. Save.';
$pageDesc = $pageDesc ?? 'A real-time surplus food donation & rescue platform connecting donors, NGOs, and volunteers.';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js (for dashboards) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- App Styles -->
    <link rel="stylesheet" href="/assets/css/index.css">

    <!-- Global Scroll Reveal -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const reveals = document.querySelectorAll('.reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        // Counters within revealed sections
                        const counters = entry.target.querySelectorAll('.count-up');
                        counters.forEach(c => {
                            if (!c.getAttribute('data-started')) {
                                startCountInside(c);
                            }
                        });

                        // Progress bars within revealed sections
                        const bars = entry.target.querySelectorAll('.count-bar');
                        bars.forEach(b => {
                            if (!b.style.width || b.style.width === '0%') {
                                b.style.transition = 'width 1.5s cubic-bezier(0.1, 0, 0.1, 1)';
                                b.style.width = b.getAttribute('data-width');
                            }
                        });
                    }
                });
            }, { threshold: 0.1 });

            function startCountInside(counter) {
                counter.setAttribute('data-started', 'true');
                const target = +counter.getAttribute('data-target');
                let count = 0;
                const updateCount = () => {
                    const increment = target / 40;
                    if (count < target) {
                        count += increment;
                        counter.innerText = Math.ceil(count).toLocaleString();
                        requestAnimationFrame(updateCount);
                    } else {
                        counter.innerText = target.toLocaleString();
                    }
                };
                updateCount();
            }

            reveals.forEach(r => observer.observe(r));
        });

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

</head>

<body>