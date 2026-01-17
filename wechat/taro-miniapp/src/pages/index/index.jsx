import { View, Text, Button } from '@tarojs/components'
import Taro from '@tarojs/taro'
import './index.scss'

export default function Index () {
  const goDoctor = () => Taro.navigateTo({ url: '/pages/doctor-register/index' })
  const goPatient = () => Taro.navigateTo({ url: '/pages/patient-bind/index' })
  const goScan = () => Taro.navigateTo({ url: '/pages/scan/index' })

  return (
    <View className='container'>
      <View className='header'>
        <Text className='title'>OpenEMR 医疗系统</Text>
        <Text className='subtitle'>请选择您的身份</Text>
      </View>

      <View className='options'>
        <View className='option-card' onClick={goDoctor}>
          <Text className='option-title'>医生注册</Text>
          <Text className='option-desc'>扫码完成医生账号注册</Text>
        </View>

        <View className='option-card' onClick={goPatient}>
          <Text className='option-title'>患者绑定</Text>
          <Text className='option-desc'>扫码绑定您的医生</Text>
        </View>
      </View>

      <Button className='scan-btn' onClick={goScan}>扫码</Button>
    </View>
  )
}
