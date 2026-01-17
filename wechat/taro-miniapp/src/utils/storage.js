import Taro from '@tarojs/taro'

export const storage = {
  set (key, value) {
    try {
      Taro.setStorageSync(key, value)
      return true
    } catch (e) {
      console.error('存储失败', e)
      return false
    }
  },
  get (key) {
    try {
      return Taro.getStorageSync(key)
    } catch (e) {
      console.error('读取失败', e)
      return null
    }
  },
  remove (key) {
    try {
      Taro.removeStorageSync(key)
      return true
    } catch (e) {
      console.error('删除失败', e)
      return false
    }
  },
  clear () {
    try {
      Taro.clearStorageSync()
      return true
    } catch (e) {
      console.error('清空失败', e)
      return false
    }
  }
}
