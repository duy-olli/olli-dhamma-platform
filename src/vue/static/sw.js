importScripts('/_nuxt/workbox.476439e0.js')

const workboxSW = new self.WorkboxSW({
  "cacheId": "oll-music",
  "clientsClaim": true,
  "directoryIndex": "/"
})

workboxSW.precache([
  {
    "url": "/_nuxt/app.e4471604a183fbe31e2d.js",
    "revision": "afdb37709961b5d0d65cad9323a541a1"
  },
  {
    "url": "/_nuxt/common.37e03f201ecc4b1b3ba6.js",
    "revision": "defea09702a90b5ae80ce9c1f8724875"
  },
  {
    "url": "/_nuxt/layouts/admin.5cb86952f95945e02b98.js",
    "revision": "7923ec6ad2aff2de5a4877f799094860"
  },
  {
    "url": "/_nuxt/layouts/default.7f480542ad84cc90216a.js",
    "revision": "a864760c61291539dd35747a365d2817"
  },
  {
    "url": "/_nuxt/layouts/site.f975be2cd322bda3ec17.js",
    "revision": "9d39556285f2caf3a41ad7d7cc4edef5"
  },
  {
    "url": "/_nuxt/manifest.74ace21d5ab8d7be0954.js",
    "revision": "1c64188a9de923c86145461a8e77d5db"
  },
  {
    "url": "/_nuxt/pages/403.350cb74baeb98f794a6a.js",
    "revision": "3f90906b6e7da0e706a6c53eae2baaa5"
  },
  {
    "url": "/_nuxt/pages/404.24051bbc933410dc0ca8.js",
    "revision": "cf33052d75b6bb9d5e708f66bf9861f8"
  },
  {
    "url": "/_nuxt/pages/admin/dashboard/index.28a8b73f5170ec565f06.js",
    "revision": "cc38d9e7a93adc4693f616e0229f9617"
  },
  {
    "url": "/_nuxt/pages/admin/login/index.1b85faaad8a9a2ca6c86.js",
    "revision": "23168d42901167722ef9f44d255f5a42"
  },
  {
    "url": "/_nuxt/pages/admin/song/index.fa4c420fde03d4b1fdbf.js",
    "revision": "e9b1405442f2740a51fe82ca87a808a1"
  },
  {
    "url": "/_nuxt/pages/admin/user/changepassword.034f112a4a8a0f4c3856.js",
    "revision": "962d5c0283087cc6a9347eda7d6ddba3"
  },
  {
    "url": "/_nuxt/pages/admin/user/create.c628caad9163907c89ec.js",
    "revision": "e38774ea7c2be561a2d77aa74bd98e45"
  },
  {
    "url": "/_nuxt/pages/admin/user/edit/_id.12cf9d3af5b58dd34308.js",
    "revision": "bde81d5e7ba4becf092eeb8c16d60043"
  },
  {
    "url": "/_nuxt/pages/admin/user/index.e68139929662e953aec2.js",
    "revision": "0cc5f97e039c179fa5262e1ff3c8745b"
  },
  {
    "url": "/_nuxt/pages/admin/user/logout.ccf59eb4694405f57e58.js",
    "revision": "473d2c37f859c9882d33d5b2bc0a63ed"
  },
  {
    "url": "/_nuxt/pages/index.305dda194b60d08fe46c.js",
    "revision": "236d2281012abb8b26839c7a785991df"
  },
  {
    "url": "/_nuxt/service-worker.js",
    "revision": "4f7faca60efb19b54c673336a49e68fc"
  }
])


workboxSW.router.registerRoute(new RegExp('/_nuxt/.*'), workboxSW.strategies.cacheFirst({}), 'GET')

workboxSW.router.registerRoute(new RegExp('/.*'), workboxSW.strategies.networkFirst({}), 'GET')

