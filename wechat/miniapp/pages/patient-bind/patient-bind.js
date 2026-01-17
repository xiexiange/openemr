// pages/patient-bind/patient-bind.js
const api = require('../../utils/api');
const wechat = require('../../utils/wechat');

Page({
  data: {
    code: '',
    doctorId: '',
    openid: '',
    sexOptions: ['Male', 'Female'],
    sexIndex: 0,
    today: '',
    formData: {
      fname: '',
      lname: '',
      DOB: '',
      sex: 'Male',
      phone: '',
      email: ''
    },
    errors: {}
  },

  onLoad(options) {
    if (options.code) {
      this.setData({ code: options.code });
    }
    if (options.doctor_id) {
      this.setData({ doctorId: options.doctor_id });
    }
    // 获取 openid
    const app = getApp();
    this.setData({ openid: app.globalData.openid || '' });
    
    // 设置今天的日期
    const today = new Date();
    const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    this.setData({ today: todayStr });
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
   * 日期选择
   */
  onDateChange(e) {
    this.setData({
      'formData.DOB': e.detail.value,
      'errors.DOB': ''
    });
  },

  /**
   * 性别选择
   */
  onSexChange(e) {
    const index = parseInt(e.detail.value);
    this.setData({
      'formData.sex': this.data.sexOptions[index],
      'sexIndex': index,
      'errors.sex': ''
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
      wechat.showLoading('绑定中...');
      
      const data = {
        code: this.data.code,
        doctor_id: this.data.doctorId,
        openid: this.data.openid,
        ...this.data.formData
      };

      const res = await api.registerPatient(data);

      if (res.success) {
        wechat.showToast('绑定成功', 'success');
        setTimeout(() => {
          wx.navigateBack();
        }, 2000);
      } else {
        wechat.showToast(res.error || '绑定失败');
      }

      wechat.hideLoading();
    } catch (err) {
      wechat.hideLoading();
      wechat.showToast('绑定失败，请重试');
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
    if (!formData.DOB) {
      errors.DOB = '请选择出生日期';
    }
    if (!formData.sex) {
      errors.sex = '请选择性别';
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
