import { View, Text, Input, Button } from '@tarojs/components'
import { useEffect, useState } from 'react'
import Taro from '@tarojs/taro'
import { api } from '../../utils/api'
import { wxapi } from '../../utils/wechat'
import './index.scss'

export default function DoctorRegister () {
  const [code, setCode] = useState('')
  const [openid, setOpenid] = useState('')
  const [formData, setFormData] = useState({
    fname: '',
    lname: '',
    email: '',
    phone: '',
    specialty: ''
  })
  const [errors, setErrors] = useState({})

  useEffect(() => {
    const params = Taro.getCurrentInstance().router?.params || {}
    if (params.code) setCode(params.code)
    // TODO: 需要从登录/绑定流程获得真实 openid，目前占位
  }, [])

  const onInput = (key, value) => {
    setFormData(prev => ({ ...prev, [key]: value }))
    setErrors(prev => ({ ...prev, [key]: '' }))
  }

  const validate = () => {
    const err = {}
    if (!formData.fname) err.fname = '请输入名字'
    if (!formData.lname) err.lname = '请输入姓氏'
    if (!formData.email) err.email = '请输入邮箱'
    else if (!/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(formData.email)) err.email = '邮箱格式不正确'
    if (!formData.phone) err.phone = '请输入手机号'
    else if (!/^1[3-9]\\d{9}$/.test(formData.phone)) err.phone = '手机号格式不正确'
    setErrors(err)
    return Object.keys(err).length === 0
  }

  const submit = async () => {
    if (!validate()) return
    try {
      wxapi.showLoading('注册中...')
      const res = await api.registerDoctor({
        code,
        openid: openid || code, // 待后端 openid 接口替换
        ...formData
      })
      if (res.data?.success) {
        wxapi.showToast('注册成功', 'success')
        setTimeout(() => Taro.navigateBack(), 1500)
      } else {
        wxapi.showToast(res.data?.error || '注册失败')
      }
    } catch (e) {
      console.error(e)
      wxapi.showToast('注册失败，请重试')
    } finally {
      wxapi.hideLoading()
    }
  }

  return (
    <View className='container'>
      <View className='header'>
        <Text className='title'>医生注册</Text>
        <Text className='subtitle'>请填写您的信息</Text>
      </View>
      <View className='form'>
        <View className='form-group'>
          <Text className='form-label'>名字 *</Text>
          <Input className='form-input' value={formData.fname} onInput={e => onInput('fname', e.detail.value)} placeholder='请输入名字' />
          {errors.fname && <Text className='error-text'>{errors.fname}</Text>}
        </View>
        <View className='form-group'>
          <Text className='form-label'>姓氏 *</Text>
          <Input className='form-input' value={formData.lname} onInput={e => onInput('lname', e.detail.value)} placeholder='请输入姓氏' />
          {errors.lname && <Text className='error-text'>{errors.lname}</Text>}
        </View>
        <View className='form-group'>
          <Text className='form-label'>邮箱 *</Text>
          <Input className='form-input' value={formData.email} onInput={e => onInput('email', e.detail.value)} placeholder='请输入邮箱' />
          {errors.email && <Text className='error-text'>{errors.email}</Text>}
        </View>
        <View className='form-group'>
          <Text className='form-label'>手机号 *</Text>
          <Input className='form-input' type='number' value={formData.phone} onInput={e => onInput('phone', e.detail.value)} placeholder='请输入手机号' />
          {errors.phone && <Text className='error-text'>{errors.phone}</Text>}
        </View>
        <View className='form-group'>
          <Text className='form-label'>专业</Text>
          <Input className='form-input' value={formData.specialty} onInput={e => onInput('specialty', e.detail.value)} placeholder='请输入专业（可选）' />
        </View>
        <Button className='btn-primary submit-btn' onClick={submit}>提交注册</Button>
      </View>
    </View>
  )
}
