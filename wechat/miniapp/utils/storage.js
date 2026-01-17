// utils/storage.js

/**
 * 本地存储封装
 */
class Storage {
  /**
   * 设置数据
   */
  set(key, value) {
    try {
      wx.setStorageSync(key, value);
      return true;
    } catch (e) {
      console.error('存储失败', e);
      return false;
    }
  }

  /**
   * 获取数据
   */
  get(key) {
    try {
      return wx.getStorageSync(key);
    } catch (e) {
      console.error('读取失败', e);
      return null;
    }
  }

  /**
   * 删除数据
   */
  remove(key) {
    try {
      wx.removeStorageSync(key);
      return true;
    } catch (e) {
      console.error('删除失败', e);
      return false;
    }
  }

  /**
   * 清空所有数据
   */
  clear() {
    try {
      wx.clearStorageSync();
      return true;
    } catch (e) {
      console.error('清空失败', e);
      return false;
    }
  }
}

module.exports = new Storage();
