// pages/index/index.js
const api = require('../../utils/api');
const wechat = require('../../utils/wechat');

Page({
  data: {
    userType: null // 'doctor' 或 'patient'
  },

  onLoad() {
    // 检查是否已登录
    this.checkLogin();
  },

  /**
   * 检查登录状态
   */
  checkLogin() {
    // TODO: 检查本地存储的登录状态
  },

  /**
   * 选择医生注册
   */
  chooseDoctor() {
    wx.navigateTo({
      url: '/pages/doctor-register/doctor-register'
    });
  },

  /**
   * 选择患者绑定
   */
  choosePatient() {
    wx.navigateTo({
      url: '/pages/patient-bind/patient-bind'
    });
  },

  /**
   * 扫码
   */
  scanCode() {
    wx.navigateTo({
      url: '/pages/scan/scan'
    });
  }
});
