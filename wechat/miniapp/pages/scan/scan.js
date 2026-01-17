// pages/scan/scan.js
const api = require('../../utils/api');
const wechat = require('../../utils/wechat');

Page({
  data: {
    qrCode: null,
    openid: null
  },

  onLoad() {
    // 获取微信登录凭证
    this.getWeChatLogin();
  },

  /**
   * 获取微信登录凭证并换取 openid
   */
  async getWeChatLogin() {
    try {
      wechat.showLoading('获取登录信息...');
      const code = await wechat.login();
      
      // TODO: 调用后端接口，用 code 换取 openid
      // 这里需要后端实现一个接口来换取 openid
      // const res = await api.exchangeOpenId(code);
      // this.setData({ openid: res.openid });
      
      wechat.hideLoading();
    } catch (err) {
      wechat.hideLoading();
      wechat.showToast('获取登录信息失败');
      console.error(err);
    }
  },

  /**
   * 扫码
   */
  async scanCode() {
    try {
      wechat.showLoading('扫码中...');
      const result = await wechat.scanCode();
      
      // 解析二维码内容（假设格式为：WX1234567890）
      const qrCode = result;
      this.setData({ qrCode });
      
      // 绑定微信到二维码
      if (this.data.openid) {
        await this.bindWeChat(qrCode);
      } else {
        wechat.showToast('请先获取登录信息');
      }
      
      wechat.hideLoading();
    } catch (err) {
      wechat.hideLoading();
      if (err.errMsg && err.errMsg.includes('cancel')) {
        // 用户取消扫码
        return;
      }
      wechat.showToast('扫码失败');
      console.error(err);
    }
  },

  /**
   * 绑定微信到二维码
   */
  async bindWeChat(qrCode) {
    try {
      wechat.showLoading('绑定中...');
      const res = await api.bindWeChat(qrCode, this.data.openid);
      
      if (res.success) {
        // 根据类型跳转到对应页面
        if (res.data.type === 'doctor') {
          wx.redirectTo({
            url: `/pages/doctor-register/doctor-register?code=${qrCode}`
          });
        } else if (res.data.type === 'patient') {
          wx.redirectTo({
            url: `/pages/patient-bind/patient-bind?code=${qrCode}&doctor_id=${res.data.doctor_id}`
          });
        }
      } else {
        wechat.showToast(res.error || '绑定失败');
      }
      
      wechat.hideLoading();
    } catch (err) {
      wechat.hideLoading();
      wechat.showToast('绑定失败');
      console.error(err);
    }
  }
});
