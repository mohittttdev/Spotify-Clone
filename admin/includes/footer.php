
<?php

?>

            </section>
        

        </main>
        <!-- End Main Content -->

    </div>
    <!-- End Admin Wrapper -->


    <!-- Bootstrap JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

    <!-- Common Admin JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // Auto-hide alerts after 4 seconds
            const alerts = document.querySelectorAll(".alert");

            alerts.forEach(function (alert) {

                setTimeout(function () {

                    if (typeof bootstrap !== "undefined") {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                        bsAlert.close();
                    } else {
                        alert.remove();
                    }

                }, 4000);

            });

        });
    </script>

</body>

</html>