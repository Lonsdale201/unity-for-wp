(function($) {
    // A dokumentum betöltődésekor
    $(document).ready(function() {
        // Input mezők változásának figyelése
        $('.unity-project-name').on('change', function() {
            console.log('Input changed for build:', $(this).data('build-id'));
            var buildId = $(this).data('build-id');
            var buildName = $(this).val();

            var data = new FormData();
            data.append('action', 'update_unity_build_name');
            data.append('nonce', unityAjax.nonce);
            data.append('build_id', buildId);
            data.append('build_name', buildName);

            fetch(unityAjax.ajaxurl, {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                if(result.success) {
                    console.log('Project name updated for build ' + buildId + ': ' + result.data.build_name);
                } else {
                    console.error('Error updating project name:', result.data);
                }
            })
            .catch(error => {
                console.error('AJAX error:', error);
            });
        });

        // Tab váltás kezelése kattintásra
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $('.tab-content').hide();
            $(this).addClass('nav-tab-active');
            var target = $(this).attr('href');
            $(target).show();
            // Frissítjük az URL-t is
            history.pushState(null, null, target);
        });

        var hash = window.location.hash;
        if(hash) {
            var $tab = $('.nav-tab[href="' + hash + '"]');
            if($tab.length) {
                $('.nav-tab').removeClass('nav-tab-active');
                $('.tab-content').hide();
                $tab.addClass('nav-tab-active');
                $(hash).show();
            }
        }

        // Reupload modális ablak kezelése
        var $modal = $('#reuploadModal');
        var $overlay = $('#modalOverlay');

        $('.reupload-link').on('click', function(e) {
            e.preventDefault();
            var buildId = $(this).data('build-id');
            $('#modalBuildId').val(buildId);
            $modal.show();
            $overlay.show();
        });

        $('#closeReuploadModal, #modalOverlay').on('click', function() {
            $modal.hide();
            $overlay.hide();
        });
    });
})(jQuery);
