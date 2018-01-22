import axios from 'axios'
import https from 'https'
import { Message } from 'element-ui'

const service = axios.create({
  baseURL: process.env.API_URL,
  timeout: 60000,
  httpsAgent: new https.Agent({
    rejectUnauthorized: false
  }),
  withCredentials: true
})

// request
service.interceptors.request.use(
  config => {
    return config
  },
  error => {
    console.log(error) // for debug

    Promise.reject(error)
  }
)

// respone
service.interceptors.response.use(
  response => response,
  error => {
    Message({
      showClose: true,
      message: error.response.data.message,
      type: 'error',
      duration: 2 * 1000
    })

    return Promise.reject(error)
  }
)

export default service
