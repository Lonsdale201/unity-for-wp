document.addEventListener('DOMContentLoaded', function () {
    // Global flag to ensure loader is only loaded once.
    if (typeof window.unityLoaderLoaded === 'undefined') {
        window.unityLoaderLoaded = false;
    }
    
    var containers = document.querySelectorAll('[id^="unityContainer-"]');
    containers.forEach(function (container) {
        var canvas = container.querySelector('canvas');
        if (!canvas) {
            console.error('Canvas not found in container:', container);
            return;
        }
    
        var containerId = container.id;
        var parts = containerId.split('-');
        var buildId = parts[1];
        
        var configScript = document.getElementById('unityConfig-' + buildId);
        if (!configScript) {
            console.error('Configuration script not found for build ID:', buildId);
            return;
        }
        
        var config;
        try {
            config = JSON.parse(configScript.textContent);
        } catch (e) {
            console.error('Invalid JSON configuration for build ID:', buildId, e);
            return;
        }
        
        if (config.autostart === false) {
            var startButton = document.getElementById('startUnityButton-' + buildId);
            if (startButton) {
                startButton.addEventListener('click', function () {
                    startButton.style.display = 'none';
                    var placeholder = document.getElementById('unityPlaceholder-' + buildId);
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    if (typeof createUnityInstance !== 'function' && !window.unityLoaderLoaded) {
                        window.unityLoaderLoaded = true;
                        var loaderScript = document.createElement('script');
                        loaderScript.src = config.loaderUrl;
                        loaderScript.onload = function () {
                            createUnityInstance(canvas, config)
                                .then(function (instance) {
                                    var ph = document.getElementById('unityPlaceholder-' + buildId);
                                    if (ph) {
                                        ph.style.display = 'none';
                                    }
                                    var unityInitializedEvent = new CustomEvent('unityInitialized', {
                                        detail: { unityInstance: instance, buildId: buildId }
                                    });
                                    document.dispatchEvent(unityInitializedEvent);
                                })
                                .catch(function (error) {
                                    console.error('Failed to load Unity instance for build ID:', buildId, error);
                                });
                        };
                        document.body.appendChild(loaderScript);
                    } else {
                        createUnityInstance(canvas, config)
                            .then(function (instance) {
                                var ph = document.getElementById('unityPlaceholder-' + buildId);
                                if (ph) {
                                    ph.style.display = 'none';
                                }
                                var unityInitializedEvent = new CustomEvent('unityInitialized', {
                                    detail: { unityInstance: instance, buildId: buildId }
                                });
                                document.dispatchEvent(unityInitializedEvent);
                            })
                            .catch(function (error) {
                                console.error('Failed to load Unity instance for build ID:', buildId, error);
                            });
                    }
                });
            } else {
                console.error('Start button not found for build ID:', buildId);
            }
        } else {
            if (typeof createUnityInstance !== 'function' && !window.unityLoaderLoaded) {
                window.unityLoaderLoaded = true;
                var loaderScript = document.createElement('script');
                loaderScript.src = config.loaderUrl;
                loaderScript.onload = function () {
                    createUnityInstance(canvas, config)
                        .then(function (instance) {
                            var unityInitializedEvent = new CustomEvent('unityInitialized', {
                                detail: { unityInstance: instance, buildId: buildId }
                            });
                            document.dispatchEvent(unityInitializedEvent);
                        })
                        .catch(function (error) {
                            console.error('Failed to load Unity instance for build ID:', buildId, error);
                        });
                };
                document.body.appendChild(loaderScript);
            } else {
                createUnityInstance(canvas, config)
                    .then(function (instance) {
                        var unityInitializedEvent = new CustomEvent('unityInitialized', {
                            detail: { unityInstance: instance, buildId: buildId }
                        });
                        document.dispatchEvent(unityInitializedEvent);
                    })
                    .catch(function (error) {
                        console.error('Failed to load Unity instance for build ID:', buildId, error);
                    });
            }
        }
    });
});
