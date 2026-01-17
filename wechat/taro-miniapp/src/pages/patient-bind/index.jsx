import { View, Text, Input, Picker, Button } from '@tarojs/components'
import { useEffect, useState } from 'react'
import Taro from '@tarojs/taro'
import { api } from '../../utils/api'
import { wxapi } from '../../utils/wechat'
import './index.scss'

export default function PatientBind () {
  const [code, setCode] = useState('')
  const [doctorId, setDoctorId] = useState('')
  const [openid, setOpenid] = useState('')
  const [sexOptions] = useState(['Male', 'Female'])
  const [sexIndex, setSexIndex] = useState(0)
  const [today, setToday] = useState('')
  const [formData, setFormData] = useState({
    fname: '',
    lname: '',
    DOB: '',
    sex: 'Male',
    phone: '',
    email: ''
  })
  const [errors, setErrors] = useState({})

  useEffect(() => {
    const params = Taro.getCurrentInstance().router?.params || {}
    if (params.code) setCode(params.code)
    if (params.doctor_id) setDoctorId(params.doctor_id)
    const t = new Date()
    const ts = `${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2, '0')}-${String(t.getDate()).padStart(2, '0')}`
    setToday(ts)
  }, [])

  const onInput = (key, value) => {
    setFormData(prev => ({ ...prev, [key]: value }))
    setErrors(prev => ({ ...prev, [key]: '' }))
  }

  const onDateChange = (e) => {
    setFormData(prev => ({ ...prev, DOB: e.detail.value }))
    setErrors(prev => ({ ...prev, DOB: '' }))
  }

  const onSexChange = (e) => {
    const idx = parseInt(e.detail.value)
    setSexIndex(idx)
    setFormData(prev => ({ ...prev, sex: sexOptions[idx] }))
    setErrors(prev => ({ ...prev, sex: '' }))
  }

  const validate = () => {
    const err = {}
    if (!formData.fname) err.fname = '请输入名字'
    if (!formData.lname) err.lname = '请输入姓氏'
    if (!formData.DOB) err.DOB = '请选择出生日期'
    if (!formData.sex) err.sex = '请选择性别'
    if (!formData.phone) err.phone = '请输入手机号'
    else if (!/^1[3-9]\\d{9}$/.test(formData.phone)) err.phone = '手机号格式不正确'
    setErrors(err)
    return Object.keys(err).length === 0
  }

  const submit = async () => {
    if (!validate()) return
    try {
      wxapi.showLoading('绑定中...')
      const res = await api.registerPatient({
        code,
        doctor_id: doctorId,
        openid: openid || code, // 待后端 openid 接口替换
        ...formData
      })
      if (res.data?.success) {
        wxapi.showToast('绑定成功', 'success')
        setTimeout(() => Taro.navigateBack(), 1500)
      } else {
        wxapi.showToast(res.data?.error || '绑定失败')
      }
    } catch (e) {
      console.error(e)
      wxapi.showToast('绑定失败，请重试')
    } finally {
      wxapi.hideLoading()
    }
  }

  return (
    <View className='container'>
      <View className='header'>
        <Text className='title'>患者绑定</Text>
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
          <Text className='form-label'>出生日期 *</Text>
          <Picker mode='date' value={formData.DOB} start='1900-01-01' end={today} onChange={onDateChange}>
            <View className='form-input picker-input'>{formData.DOB || '请选择出生日期'}</View>
          </Picker>
          {errors.DOB && <Text className='error-text'>{errors.DOB}</Text>}
        </View>
        <View className='form-group'>
          <Text className='form-label'>性别 *</Text>
          <Picker mode='selector' range={sexOptions} value={sexIndex} onChange={onSexChange}>
            <View className='form-input picker-input'>{formData.sex || '请选择性别'}</View>
          </Picker>
          {errors.sex && <Text className='error-text'>{errors.sex}</Text>}
        </View>
        <View className='form-group'>
          <Text className='form-label'>手机号 *</Text>
          <Input className='form-input' type='number' value={formData.phone} onInput={e => onInput('phone', e.detail.value)} placeholder='请输入手机号' />
          {errors.phone && <Text className='error-text'>{errors.phone}</Text>}
        </View>
        <View className='form-group'>
          <Text className='form-label'>邮箱</Text>
          <Input className='form-input' value={formData.email} onInput={e => onInput('email', e.detail.value)} placeholder='请输入邮箱（可选）' />
        </View>
        <Button className='btn-primary submit-btn' onClick={submit}>提交绑定</Button>
      </View>
    </View>
  )
}
