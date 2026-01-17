import { View, Text, Button } from '@tarojs/components'
import { useState } from 'react'
import { api } from '../../utils/api'
import { wxapi } from '../../utils/wechat'
import Taro from '@tarojs/taro'
import './index.scss'

export default function ScanPage () {
  const [qrCode, setQrCode] = useState('')
  const [openid, setOpenid] = useState('')

  const ensureLogin = async () => {
    if (openid) return openid
    const code = await wxapi.login()
    // TODO: 需要后端提供 code -> openid 的接口，这里暂存 code
    setOpenid(code)
    return code
  }

  const handleScan = async () => {
    try {
      wxapi.showLoading('扫码中...')
      const code = await ensureLogin()
      const res = await wxapi.scanCode()
      const qr = res.result || res
      setQrCode(qr)

      // 绑定微信到二维码（用 code 代替 openid，待后端提供实际 openid 接口）
      const bindRes = await api.bindWeChat(qr, code)
      if (bindRes.data?.success) {
        const { type, doctor_id } = bindRes.data.data || {}
        if (type === 'doctor') {
          Taro.redirectTo({ url: `/pages/doctor-register/index?code=${qr}` })
        } else if (type === 'patient') {
          Taro.redirectTo({ url: `/pages/patient-bind/index?code=${qr}&doctor_id=${doctor_id || ''}` })
        } else {
          wxapi.showToast('二维码类型未知')
        }
      } else {
        wxapi.showToast(bindRes.data?.error || '绑定失败')
      }
    } catch (e) {
      if (e?.errMsg?.includes('cancel')) return
      wxapi.showToast('扫码失败')
      console.error(e)
    } finally {
      wxapi.hideLoading()
    }
  }

  return (
    <View className='container'>
      <View className='scan-area'>
        <Text className='scan-icon'>📷</Text>
        <Text className='scan-tip'>点击下方按钮开始扫码</Text>
      </View>
      <Button className='scan-btn btn-primary' onClick={handleScan}>开始扫码</Button>
      {qrCode && <Text className='scan-tip'>二维码：{qrCode}</Text>}
    </View>
  )
}
