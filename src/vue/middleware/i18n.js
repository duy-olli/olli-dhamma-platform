import { getLocaleFromCookie, getLocaleFromLocalStorage } from '~/utils/locale'

export default function({ isServer, app, store, req }) {
  // If nuxt generate, pass this middleware
  if (isServer && !req) return

  // Get locale from cookie
  let locale = isServer ? getLocaleFromCookie(req) : getLocaleFromLocalStorage()

  if (typeof locale === 'undefined') {
    locale = 'en'
  }

  // Set locale
  store.commit('SET_LANG', locale)
  app.i18n.locale = store.state.locale
}
