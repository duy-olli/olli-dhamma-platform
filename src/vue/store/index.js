import { setToken } from '~/utils/auth'
import {
  loginByEmail,
  loginByFacebook,
  loginByGoogle,
  register,
  activate,
  changePassword,
  checkEmail,
  forgotPassword,
  checkForgot,
  resetPassword,
  updatePasswordCode,
  checkUpdatePassword,
  updatePassword
} from '~/api/users'

// Polyfill for window.fetch()
require('whatwg-fetch')

export const state = () => ({
  authUser: null,
  authToken: '',
  locales: ['en', 'vi'],
  locale: 'en'
})

export const mutations = {
  SET_USER(state, authData) {
    state.authUser = authData.user || null
    state.authToken = authData.token
  },
  SET_LANG(state, locale) {
    if (state.locales.indexOf(locale) !== -1) {
      state.locale = locale
    }
  }
}

export const actions = {
  login_by_email({ commit }, formData) {
    const email = formData.email.trim()

    return loginByEmail(email, formData.password).then(res => {
      setToken(res.data.response)
    })
  },

  login_by_facebook({ commit }, formData) {
    return loginByFacebook(formData).then(res => {
      setToken(res.data.response)
    })
  },

  login_by_google({ commit }, formData) {
    return loginByGoogle(formData).then(res => {
      setToken(res.data.response)
    })
  },

  register({ commit }, formData) {
    return register(formData)
  },

  activate({ commit }, formData) {
    return activate(formData).then(res => {
      setToken(res.data.response)
    })
  },

  changepassword({ commit }, { authToken, formData }) {
    return changePassword(authToken, formData).then(res => {
      setToken(res.data.response)
    })
  },

  check_email({ commit }, formData) {
    const email = formData.email.trim()

    return checkEmail(email).then(res => {
      return res
    })
  },

  send_forgot_code({ commit }, email) {
    return forgotPassword(email)
  },

  check_forgot({ commit }, formData) {
    return checkForgot(formData).then(res => {
      return res
    })
  },

  reset_password({ commit }, formData) {
    return resetPassword(formData).then(res => {
      return res
    })
  },

  send_update_code({ commit }, email) {
    return updatePasswordCode(email)
  },

  check_update_password({ commit }, formData) {
    return checkUpdatePassword(formData).then(res => {
      return res
    })
  },

  update_password({ commit }, formData) {
    return updatePassword(formData).then(res => {
      return res
    })
  }
}

export const getters = {
  isAuthenticated(state) {
    return !!state.authUser
  },
  loggedUser(state) {
    return state.authUser
  },
  loggedToken(state) {
    return state.authToken
  }
}
