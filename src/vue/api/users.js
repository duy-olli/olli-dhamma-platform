import fetch from '~/utils/fetch'

export function loginByEmail(email, password) {
  return fetch({
    url: '/v1/users/login/email',
    method: 'post',
    data: {
      email,
      password
    }
  })
}

export function loginByFacebook(responeFromFacebook) {
  return fetch({
    url: '/v1/users/login/facebook',
    method: 'post',
    data: {
      email: responeFromFacebook.formData.oauthInfo.email,
      password: responeFromFacebook.formData
    }
  })
}

export function loginByGoogle(responeFromGoogle) {
  return fetch({
    url: '/v1/users/login/google',
    method: 'post',
    data: {
      email: responeFromGoogle.formData.oauthInfo.email,
      password: responeFromGoogle.formData
    }
  })
}

export function getAll(jwt, query) {
  return fetch({
    url: '/v1/users',
    method: 'get',
    params: query,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function get(jwt, id) {
  return fetch({
    url: `/v1/users/${id}`,
    method: 'get',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function create(jwt, formData) {
  return fetch({
    url: '/v1/users',
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function update(jwt, id, formData) {
  return fetch({
    url: `/v1/users/${id}`,
    method: 'put',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function del(jwt, id) {
  return fetch({
    url: '/v1/users',
    method: 'delete',
    data: {
      id: id
    },
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function bulk(jwt, formData) {
  return fetch({
    url: '/v1/users/bulk',
    method: 'post',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function register(formData) {
  return fetch({
    url: '/v1/users/register',
    method: 'post',
    data: formData
  })
}

export function activate(formData) {
  return fetch({
    url: '/v1/users/activate',
    method: 'post',
    data: formData
  })
}

export function changePassword(jwt, formData) {
  return fetch({
    url: '/v1/users/changepassword',
    method: 'put',
    data: formData,
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function forgotPassword(email) {
  return fetch({
    url: '/v1/users/forgotpassword',
    method: 'post',
    data: {
      email: email
    }
  })
}

export function checkChangePassword(jwt) {
  return fetch({
    url: '/v1/users/checkchangepassword',
    method: 'post',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function getFormSource(jwt) {
  return fetch({
    url: '/v1/users/formsource',
    method: 'get',
    headers: {
      Authorization: 'Bearer ' + jwt
    }
  })
}

export function checkEmail(email) {
  return fetch({
    url: '/v1/users/checkemail',
    method: 'post',
    data: {
      email
    }
  })
}

export function checkForgot(formData) {
  return fetch({
    url: '/v1/users/checkforgot',
    method: 'post',
    data: formData
  })
}

export function resetPassword(formData) {
  return fetch({
    url: '/v1/users/resetpassword',
    method: 'put',
    data: formData
  })
}

export function updatePasswordCode(email) {
  return fetch({
    url: '/v1/users/updatepasswordcode',
    method: 'post',
    data: {
      email: email
    }
  })
}

export function checkUpdatePassword(formData) {
  return fetch({
    url: '/v1/users/checkupdatepassword',
    method: 'post',
    data: formData
  })
}

export function updatePassword(formData) {
  return fetch({
    url: '/v1/users/updatepassword',
    method: 'put',
    data: formData
  })
}
