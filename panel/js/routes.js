routes = [{
        path: '/',
        url: './index.html',
    },
    {
        path: '/add-device/',
        url: './pages/add_device.html',
    },
    {
        path: '/change-log/',
        url: './pages/change-log.html',
    },
    {
        path: '/block-web/',
        url: './pages/block-web.html',
    },
    {
        path: '/block-app/',
        url: './pages/block-app.html',
    },
    {
        path: '/block-title/',
        url: './pages/block-title.html',
    },
    {
        path: '/device-settings/',
        url: './pages/device-settings.html',
    },
    {
        path: '/home/',
        url: './pages/home.html',
    },
    {
        path: '/about/',
        url: './pages/about.html',
    },
    {
        path: '/catalog/',
        componentUrl: './pages/catalog.html',
    },
    {
        path: '/product/:id/',
        componentUrl: './pages/product.html',
    },
    {
        path: '/settings/',
        url: './pages/settings.html',
    },
    // Page Loaders & Router
    {
        path: '/page-loader-template7/:user/:userId/:posts/:postId/',
        templateUrl: './pages/page-loader-template7.html',
    },
    {
        path: '/page-loader-component/:user/:userId/:posts/:postId/',
        componentUrl: './pages/page-loader-component.html',
    },
    {
        path: '/request-and-load/user/:userId/',
        async: function(routeTo, routeFrom, resolve, reject) {
            // Router instance
            var router = this;

            // App instance
            var app = router.app;

            // Show Preloader
            app.preloader.show();

            // User ID from request
            var userId = routeTo.params.userId;

            // Simulate Ajax Request
            setTimeout(function() {
                // We got user data from request
                var user = {
                    firstName: userId,
                    lastName: 'Kharlampidi',
                    about: 'Hello, i am creator of Framework7! Hope you like it!',
                    links: [{
                            title: 'Framework7 Website',
                            url: 'http://framework7.io',
                        },
                        {
                            title: 'Framework7 Forum',
                            url: 'http://forum.framework7.io',
                        },
                    ]
                };
                // Hide Preloader
                app.preloader.hide();

                // Resolve route to load page
                resolve({
                    componentUrl: './pages/request-and-load.html',
                }, {
                    context: {
                        user: user,
                    }
                });
            }, 1000);
        },
    },
    // Default route (404 page). MUST BE THE LAST
    {
        path: '(.*)',
        url: './pages/404.html',
    },
];
