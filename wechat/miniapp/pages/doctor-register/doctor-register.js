// pages/doctor-register/doctor-register.js
const api = require('../../utils/api');
const wechat = require('../../utils/wechat');

Page({
  data: {
    code: '',
    openid: '',
    formData: {
      fname: '',
      lname: '',
      email: '',
      phone: '',
      specialty: ''
    },
    errors: {}
  },

  onLoad(options) {
    if (options.code) {
      this.setData({ code: options.code });
    }
    // 获取 openid（应该从扫码页面传递过来，或从全局数据获取）
    const app = getApp();
    this.setData({ openid: app.globalData.openid || '' });
  },

  /**
   * 输入框变化
   */
  onInput(e) {
    const field = e.currentTarget.dataset.field;
    const value = e.detail.value;
    this.setData({
      [`formData.${field}`]: value,
      [`errors.${field}`]: ''
    });
  },

  /**
   * 提交注册
   */
  async submit() {
    // 验证表单
    if (!this.validate()) {
      return;
    }

    try {
      wechat.showLoading('注册中...');
      
      const data = {
        code: this.data.code,
        openid: this.data.openid,
        ...this.data.formData
      };

      const res = await api.registerDoctor(data);

      if (res.success) {
        wechat.showToast('注册成功', 'success');
        setTimeout(() => {
          wx.navigateBack();
        }, 2000);
      } else {
        wechat.showToast(res.error || '注册失败');
      }

      wechat.hideLoading();
    } catch (err) {
      wechat.hideLoading();
      wechat.showToast('注册失败，请重试');
      console.error(err);
    }
  },

  /**
   * 表单验证
   */
  validate() {
    const errors = {};
    const { formData } = this.data;

    if (!formData.fname) {
      errors.fname = '请输入名字';
    }
    if (!formData.lname) {
      errors.lname = '请输入姓氏';
    }
    if (!formData.email) {
      errors.email = '请输入邮箱';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      errors.email = '邮箱格式不正确';
    }
    if (!formData.phone) {
      errors.phone = '请输入手机号';
    } else if (!/^1[3-9]\d{9}$/.test(formData.phone)) {
      errors.phone = '手机号格式不正确';
    }

    this.setData({ errors });
    return Object.keys(errors).length === 0;
  }
});
