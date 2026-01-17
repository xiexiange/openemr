// utils/wechat.js

/**
 * 微信API封装
 */
class WeChat {
  /**
   * 获取微信登录凭证
   */
  login() {
    return new Promise((resolve, reject) => {
      wx.login({
        success: (res) => {
          if (res.code) {
            resolve(res.code);
          } else {
            reject(new Error('获取登录凭证失败'));
          }
        },
        fail: reject
      });
    });
  }

  /**
   * 获取用户信息
   */
  getUserInfo() {
    return new Promise((resolve, reject) => {
      wx.getUserProfile({
        desc: '用于完善用户资料',
        success: (res) => {
          resolve(res.userInfo);
        },
        fail: reject
      });
    });
  }

  /**
   * 扫码
   */
  scanCode() {
    return new Promise((resolve, reject) => {
      wx.scanCode({
        success: (res) => {
          resolve(res.result);
        },
        fail: reject
      });
    });
  }

  /**
   * 显示提示
   */
  showToast(title, icon = 'none') {
    wx.showToast({
      title: title,
      icon: icon,
      duration: 2000
    });
  }

  /**
   * 显示加载中
   */
  showLoading(title = '加载中...') {
    wx.showLoading({
      title: title,
      mask: true
    });
  }

  /**
   * 隐藏加载
   */
  hideLoading() {
    wx.hideLoading();
  }
}

module.exports = new WeChat();
