import Taro from '@tarojs/taro'

export const wxapi = {
  async login () {
    const res = await Taro.login()
    return res.code
  },
  async getUserProfile () {
    return Taro.getUserProfile({ desc: '用于完善用户资料' })
  },
  async scanCode () {
    return Taro.scanCode({})
  },
  showToast (title, icon = 'none') {
    Taro.showToast({ title, icon, duration: 2000 })
  },
  showLoading (title = '加载中...') {
    Taro.showLoading({ title, mask: true })
  },
  hideLoading () {
    Taro.hideLoading()
  }
}
