import {
  getUserFromCookie,
  getTokenFromCookie,
  getUserFromLocalStorage,
  getTokenFromLocalStorage
} from '~/utils/auth'

export default function({ isServer, store, req }) {
  // If nuxt generate, pass this middleware
  if (isServer && !req) return

  const loggedUser = isServer
    ? getUserFromCookie(req)
    : getUserFromLocalStorage()
  const loggedToken = isServer
    ? getTokenFromCookie(req)
    : getTokenFromLocalStorage()

  store.commit('SET_USER', {
    user: loggedUser,
    token: loggedToken
  })
}
