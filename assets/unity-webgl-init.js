document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('unityCanvas')) {
        var unityInstance = null; // Unity instance var

        var config = {
            dataUrl: unityWebGLConfig.dataUrl,
            frameworkUrl: unityWebGLConfig.frameworkUrl,
            codeUrl: unityWebGLConfig.codeUrl,
            streamingAssetsUrl: unityWebGLConfig.streamingAssetsUrl,
            companyName: unityWebGLConfig.companyName,
            productName: unityWebGLConfig.productName,
            productVersion: unityWebGLConfig.productVersion,
        };

        var canvas = document.getElementById('unityCanvas');
        if (canvas) {
            var script = document.createElement('script');
            script.src = unityWebGLConfig.loaderUrl;
            script.onload = () => {
                createUnityInstance(canvas, config).then(function (instance) {
                    console.log('Unity instance loaded successfully.');
                    window.unityInstance = instance; // Global unity instance
                }).catch(function (error) {
                    console.error('Failed to load Unity instance', error);
                });
            };
            document.body.appendChild(script);
        } else {
            console.error('Unity canvas not found');
        }
    }
});
