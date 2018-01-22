import { lowerCase } from 'lodash'
import { getAll } from '~/api/dashboards'

export const state = () => ({
  data: {},
  query: {},
  formSource: {},
  totalItems: 0,
  recordPerPage: 0
})

export const mutations = {
  SET_QUERY(state) {
    state.query.orderby =
      typeof state.data.meta !== 'undefined'
        ? lowerCase(state.data.meta.orderBy)
        : 'id'
    state.query.ordertype =
      typeof state.data.meta !== 'undefined'
        ? lowerCase(state.data.meta.orderType)
        : 'desc'
    state.query.page =
      typeof state.data.meta !== 'undefined' ? state.data.meta.page : 1
  },

  SET_DATA(state, responseData) {
    state.data = responseData || null
    state.totalItems =
      typeof responseData.meta !== 'undefined'
        ? responseData.meta.totalItems
        : 0
    state.recordPerPage =
      typeof responseData.meta !== 'undefined'
        ? responseData.meta.recordPerPage
        : 0
  }
}

export const actions = {
  get_all({ commit }, { authToken, query }) {
    return getAll(authToken, query).then(res => {
      commit('SET_DATA', res.data)
      commit('SET_QUERY')
    })
  }
}
