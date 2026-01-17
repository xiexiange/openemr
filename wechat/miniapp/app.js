// app.js
App({
  globalData: {
    baseUrl: 'https://localhost/apis/default/api',
    openid: null,
    unionid: null
  },

  onLaunch() {
    // 获取微信登录凭证
    this.getWeChatLogin();
  },

  /**
   * 获取微信登录凭证
   */
  getWeChatLogin() {
    wx.login({
      success: (res) => {
        if (res.code) {
          // 这里应该调用后端接口，用 code 换取 openid
          // 暂时先存储 code，后续在需要时再换取
          this.globalData.code = res.code;
        } else {
          console.error('获取微信登录凭证失败', res.errMsg);
        }
      },
      fail: (err) => {
        console.error('微信登录失败', err);
      }
    });
  }
});
