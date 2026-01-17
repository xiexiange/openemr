import Taro from '@tarojs/taro'

const baseUrl = 'https://localhost/apis/default/api' // 保持现状，可按需修改

function request (url, method = 'GET', data = {}) {
  return Taro.request({
    url: baseUrl + url,
    method,
    data,
    header: {
      'Content-Type': 'application/json'
    }
  })
}

export const api = {
  generateDoctorQrCode: () => request('/wechat/qrcode/doctor'),
  generatePatientQrCode: (doctorId) => request('/wechat/qrcode/patient', 'GET', { doctor_id: doctorId }),
  bindWeChat: (code, openid, unionid = null) => request('/wechat/bind', 'POST', { code, openid, unionid }),
  registerDoctor: (data) => request('/wechat/doctor/register', 'POST', data),
  registerPatient: (data) => request('/wechat/patient/register', 'POST', data),
  checkQrCodeStatus: (code) => request('/wechat/qrcode/status', 'GET', { code })
}
