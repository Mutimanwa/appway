<!-- Start Footerbar -->
<div class="footerbar">
    <footer class="footer">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">© <?php echo date('Y'); ?> <a href="<?php echo ROOT_URL; ?>">PCL Lab</a>. Tous droits
                    réservés.</p>
            </div>
            <div class="col-md-6 text-right">
                <p class="mb-0">
                    <span class="badge badge-success">Version 1.0.0</span>
                    <span class="mx-2">|</span>
                    <i class="ri-time-line"></i> <?php echo date('H:i'); ?>
                </p>
            </div>
        </div>
    </footer>
</div>
<!-- End Footerbar -->
</div>
<!-- End Rightbar -->
</div>
<!-- End Containerbar -->

<!-- Start js -->
<!-- <script src="<?php echo ROOT_URL; ?>/admin/assets/js/jquery.min.js"></script> -->
<script src="<?php echo ROOT_URL; ?>/admin/assets/js/popper.min.js"></script>
<script src="<?php echo ROOT_URL; ?>/admin/assets/js/bootstrap.min.js"></script>
<script src="<?php echo ROOT_URL; ?>/admin/assets/js/modernizr.min.js"></script>
<script src="<?php echo ROOT_URL; ?>/admin/assets/js/detect.js"></script>
<script src="<?php echo ROOT_URL; ?>/admin/assets/js/jquery.slimscroll.js"></script>
<script src="<?php echo ROOT_URL; ?>/admin/assets/js/vertical-menu.js"></script>

<!-- Switchery js -->
<script src="<?php echo ROOT_URL; ?>/admin/assets/plugins/switchery/switchery.min.js"></script>

<!-- Apex js -->
<script src="<?php echo ROOT_URL; ?>/admin/assets/plugins/apexcharts/apexcharts.min.js"></script>
<script src="<?php echo ROOT_URL; ?>/admin/assets/plugins/apexcharts/irregular-data-series.js"></script>

<!-- Slick js -->
<script src="<?php echo ROOT_URL; ?>/admin/assets/plugins/slick/slick.min.js"></script>

<!-- Custom Dashboard js -->
<script src="<?php echo ROOT_URL; ?>/admin/assets/js/custom/custom-dashboard.js"></script>

<!-- Core js -->
<script src="<?php echo ROOT_URL; ?>/admin/assets/js/core.js"></script>

<script src="<?php echo ROOT_URL; ?>/admin/assets/plugins/select2/select2.min.js"></script>
<script src="<?php echo ROOT_URL; ?>/admin/assets/plugins/dropzone/dist/dropzone.js"></script>
<script src="<?php echo ROOT_URL; ?>/admin/assets/plugins/summernote/summernote-bs4.min.js"></script>
<!-- Form Step js -->
<script src="<?php echo ROOT_URL; ?>/admin/assets/plugins/jquery-step/jquery.steps.min.js"></script>
<script src="<?php echo ROOT_URL; ?>/admin/assets/js/custom/custom-form-wizard.js"></script>
<!-- Custom scripts -->
<!-- <script>
    $(document).ready(function () {
        // Initialiser les switches
        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch-setting-first'));
        elems.forEach(function (html) {
            var switchery = new Switchery(html, { size: 'small' });
        });

        // Charts (exemple)
        var options1 = {
            chart: {
                type: 'area',
                height: 50,
                sparkline: {
                    enabled: true
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            colors: ['#3498db'],
            series: [{
                name: 'Utilisateurs',
                data: [30, 40, 35, 50, 49, 60, 70, 91, 125]
            }],
            tooltip: {
                fixed: {
                    enabled: false
                },
                x: {
                    show: false
                },
                marker: {
                    show: false
                }
            }
        };

        var chart1 = new ApexCharts(document.querySelector("#apex-line-chart1"), options1);
        chart1.render();

        // Même chose pour les autres charts...
    });

    // Notification counter
    function updateNotificationCount() {
        $.ajax({
            url: '<?php echo ROOT_URL; ?>/admin/api/notifications-count.php',
            method: 'GET',
            success: function (response) {
                if (response.count > 0) {
                    $('.notifybar .live-icon').text(response.count).show();
                } else {
                    $('.notifybar .live-icon').hide();
                }
            }
        });
    }

    // Actualiser toutes les minutes
    setInterval(updateNotificationCount, 60000);
</script> -->
</body>

</html>