// utils/api.js
const app = getApp();

/**
 * API 请求封装
 */
class Api {
  constructor() {
    this.baseUrl = app.globalData.baseUrl;
  }

  /**
   * 通用请求方法
   */
  request(url, method = 'GET', data = {}) {
    return new Promise((resolve, reject) => {
      wx.request({
        url: this.baseUrl + url,
        method: method,
        data: data,
        header: {
          'Content-Type': 'application/json'
        },
        success: (res) => {
          if (res.statusCode >= 200 && res.statusCode < 300) {
            resolve(res.data);
          } else {
            reject(res);
          }
        },
        fail: (err) => {
          reject(err);
        }
      });
    });
  }

  /**
   * 生成医生注册二维码
   */
  generateDoctorQrCode() {
    return this.request('/wechat/qrcode/doctor', 'GET');
  }

  /**
   * 生成患者绑定二维码
   */
  generatePatientQrCode(doctorId) {
    return this.request('/wechat/qrcode/patient', 'GET', { doctor_id: doctorId });
  }

  /**
   * 绑定微信到二维码
   */
  bindWeChat(code, openid, unionid = null) {
    return this.request('/wechat/bind', 'POST', {
      code: code,
      openid: openid,
      unionid: unionid
    });
  }

  /**
   * 医生注册
   */
  registerDoctor(data) {
    return this.request('/wechat/doctor/register', 'POST', data);
  }

  /**
   * 患者注册
   */
  registerPatient(data) {
    return this.request('/wechat/patient/register', 'POST', data);
  }

  /**
   * 检查二维码状态
   */
  checkQrCodeStatus(code) {
    return this.request('/wechat/qrcode/status', 'GET', { code: code });
  }
}

module.exports = new Api();
