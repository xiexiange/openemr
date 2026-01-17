# OpenEMR API 集成指南 - 小程序接入

本文档梳理了 OpenEMR 对外提供的所有 API，供小程序前端接入使用。

## 目录

- [API 概览](#api-概览)
- [认证方式](#认证方式)
- [Standard API (OpenEMR REST)](#standard-api-openemr-rest)
- [FHIR API (FHIR R4)](#fhir-api-fhir-r4)
- [Patient Portal API](#patient-portal-api)
- [小程序接入建议](#小程序接入建议)
- [快速开始](#快速开始)

---

## API 概览

OpenEMR 提供三种类型的 API：

| API 类型 | 基础路径 | 用途 | 推荐场景 |
|---------|---------|------|---------|
| **Standard API** | `/apis/{site}/api` | OpenEMR 原生数据结构 | 自定义集成、管理功能 |
| **FHIR API** | `/apis/{site}/fhir` | FHIR R4 标准资源 | 医疗互操作性、标准应用 |
| **Patient Portal API** | `/apis/{site}/portal` | 患者门户 API | 患者端应用（实验性） |

**默认站点示例：**
```
https://localhost:9300/apis/default/api
https://localhost:9300/apis/default/fhir
https://localhost:9300/apis/default/portal
```

---

## 认证方式

所有 API 使用 **OAuth 2.0 + OpenID Connect** 认证。

### 1. 启用 API

**管理后台 → Config → Connectors**
- ☑ Enable OpenEMR Standard REST API
- ☑ Enable OpenEMR Standard FHIR REST API
- ☑ Enable OpenEMR Patient Portal REST API（如需要）

### 2. 配置 SSL/TLS

**必须启用 HTTPS**，设置站点地址：
**管理后台 → Config → Connectors → Site Address**
```
https://your-openemr-host
```

### 3. 注册客户端应用

#### 方式一：API 注册（推荐）

```bash
curl -X POST -k -H 'Content-Type: application/json' \
  https://localhost:9300/oauth2/default/registration \
  --data '{
    "application_type": "public",
    "redirect_uris": ["https://your-miniprogram-callback"],
    "client_name": "小程序应用",
    "token_endpoint_auth_method": "none",
    "scope": "openid offline_access api:oemr api:fhir patient/Patient.rs patient/Observation.rs patient/Appointment.rs"
  }'
```

#### 方式二：Web 界面注册

访问：`https://your-openemr-host/interface/smart/register-app.php`

### 4. 获取访问令牌

#### Authorization Code Grant（推荐用于小程序）

```http
# 步骤 1: 获取授权码
GET /oauth2/default/authorize?
  client_id=YOUR_CLIENT_ID&
  redirect_uri=YOUR_REDIRECT_URI&
  response_type=code&
  scope=openid offline_access api:fhir patient/Patient.rs&
  state=RANDOM_STATE

# 步骤 2: 交换访问令牌
POST /oauth2/default/token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code&
code=AUTHORIZATION_CODE&
redirect_uri=YOUR_REDIRECT_URI&
client_id=YOUR_CLIENT_ID
```

#### Password Grant（仅用于测试，不推荐生产环境）

```http
POST /oauth2/default/token
Content-Type: application/x-www-form-urlencoded

grant_type=password&
username=USERNAME&
password=PASSWORD&
client_id=YOUR_CLIENT_ID&
scope=openid offline_access api:fhir
```

### 5. 使用访问令牌

所有 API 请求需要在 Header 中携带令牌：

```http
GET /apis/default/fhir/Patient
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/fhir+json
```

---

## Standard API (OpenEMR REST)

**基础路径：** `https://your-host/apis/default/api`

### 资源列表

| 资源 | 方法 | 路径 | 权限 | 说明 |
|------|------|------|------|------|
| **Facility** | GET | `/api/facility` | crus | 获取设施列表 |
| | GET | `/api/facility/:fuuid` | r | 获取单个设施 |
| | POST | `/api/facility` | c | 创建设施 |
| | PUT | `/api/facility/:fuuid` | u | 更新设施 |
| **Patient** | GET | `/api/patient` | rs | 搜索患者 |
| | GET | `/api/patient/:puuid` | r | 获取患者详情 |
| | POST | `/api/patient` | c | 创建患者 |
| | PUT | `/api/patient/:puuid` | u | 更新患者 |
| **Encounter** | GET | `/api/patient/:puuid/encounter` | rs | 获取患者就诊记录 |
| | GET | `/api/patient/:puuid/encounter/:euuid` | r | 获取单个就诊记录 |
| | POST | `/api/patient/:puuid/encounter` | c | 创建就诊记录 |
| | PUT | `/api/patient/:puuid/encounter/:euuid` | u | 更新就诊记录 |
| **SOAP Note** | GET | `/api/patient/:pid/encounter/:eid/soap_note` | rs | 获取 SOAP 笔记 |
| | GET | `/api/patient/:pid/encounter/:eid/soap_note/:sid` | r | 获取单个 SOAP 笔记 |
| | POST | `/api/patient/:pid/encounter/:eid/soap_note` | c | 创建 SOAP 笔记 |
| | PUT | `/api/patient/:pid/encounter/:eid/soap_note/:sid` | u | 更新 SOAP 笔记 |
| **Vitals** | GET | `/api/patient/:pid/encounter/:eid/vital` | rs | 获取生命体征 |
| | GET | `/api/patient/:pid/encounter/:eid/vital/:vid` | r | 获取单个生命体征 |
| | POST | `/api/patient/:pid/encounter/:eid/vital` | c | 记录生命体征 |
| | PUT | `/api/patient/:pid/encounter/:eid/vital/:vid` | u | 更新生命体征 |
| **Practitioner** | GET | `/api/practitioner` | rs | 获取医生列表 |
| | GET | `/api/practitioner/:pruuid` | r | 获取医生详情 |
| | POST | `/api/practitioner` | c | 创建医生 |
| | PUT | `/api/practitioner/:pruuid` | u | 更新医生 |
| **Medical Problem** | GET | `/api/medical_problem` | rs | 获取医疗问题列表 |
| | GET | `/api/patient/:puuid/medical_problem` | rs | 获取患者医疗问题 |
| | POST | `/api/patient/:puuid/medical_problem` | c | 添加医疗问题 |
| | PUT | `/api/patient/:puuid/medical_problem/:muuid` | u | 更新医疗问题 |
| | DELETE | `/api/patient/:puuid/medical_problem/:muuid` | d | 删除医疗问题 |
| **Allergy** | GET | `/api/allergy` | rs | 获取过敏列表 |
| | GET | `/api/patient/:puuid/allergy` | rs | 获取患者过敏 |
| | POST | `/api/patient/:puuid/allergy` | c | 添加过敏 |
| | PUT | `/api/patient/:puuid/allergy/:auuid` | u | 更新过敏 |
| | DELETE | `/api/patient/:puuid/allergy/:auuid` | d | 删除过敏 |
| **Medication** | GET | `/api/patient/:pid/medication` | rs | 获取患者用药 |
| | GET | `/api/patient/:pid/medication/:mid` | r | 获取单个用药 |
| | POST | `/api/patient/:pid/medication` | c | 添加用药 |
| | PUT | `/api/patient/:pid/medication/:mid` | u | 更新用药 |
| | DELETE | `/api/patient/:pid/medication/:mid` | d | 删除用药 |
| **Surgery** | GET | `/api/patient/:pid/surgery` | rs | 获取手术记录 |
| | GET | `/api/patient/:pid/surgery/:sid` | r | 获取单个手术记录 |
| | POST | `/api/patient/:pid/surgery` | c | 添加手术记录 |
| | PUT | `/api/patient/:pid/surgery/:sid` | u | 更新手术记录 |
| | DELETE | `/api/patient/:pid/surgery/:sid` | d | 删除手术记录 |
| **Dental Issue** | GET | `/api/patient/:pid/dental_issue` | rs | 获取牙科问题 |
| | GET | `/api/patient/:pid/dental_issue/:did` | r | 获取单个牙科问题 |
| | POST | `/api/patient/:pid/dental_issue` | c | 添加牙科问题 |
| | PUT | `/api/patient/:pid/dental_issue/:did` | u | 更新牙科问题 |
| | DELETE | `/api/patient/:pid/dental_issue/:did` | d | 删除牙科问题 |
| **Appointment** | GET | `/api/appointment` | rs | 获取预约列表 |
| | GET | `/api/appointment/:eid` | r | 获取单个预约 |
| | GET | `/api/patient/:pid/appointment` | rs | 获取患者预约 |
| | GET | `/api/patient/:pid/appointment/:eid` | r | 获取患者单个预约 |
| | POST | `/api/patient/:pid/appointment` | c | 创建预约 |
| | DELETE | `/api/patient/:pid/appointment/:eid` | d | 取消预约 |
| **List** | GET | `/api/list/:list_name` | rs | 获取列表数据（只读） |
| **User** | GET | `/api/user` | rs | 获取用户列表（只读） |
| | GET | `/api/user/:uuid` | r | 获取用户详情（只读） |
| **Insurance Company** | GET | `/api/insurance_company` | rs | 获取保险公司列表 |
| | GET | `/api/insurance_company/:iid` | r | 获取保险公司详情 |
| | POST | `/api/insurance_company` | c | 创建保险公司 |
| | PUT | `/api/insurance_company/:iid` | u | 更新保险公司 |
| **Insurance Type** | GET | `/api/insurance_type` | rs | 获取保险类型（只读） |
| **Patient Document** | GET | `/api/patient/:pid/document` | rs | 获取患者文档 |
| | GET | `/api/patient/:pid/document/:did` | r | 下载文档 |
| | POST | `/api/patient/:pid/document` | c | 上传文档 |
| **Patient Employer** | GET | `/api/patient/:puuid/employer` | rs | 获取患者雇主信息（只读） |
| **Patient Insurance** | GET | `/api/patient/:puuid/insurance` | rs | 获取患者保险信息 |
| | GET | `/api/patient/:puuid/insurance/:uuid` | r | 获取单个保险信息 |
| | POST | `/api/patient/:puuid/insurance` | c | 添加保险信息 |
| | PUT | `/api/patient/:puuid/insurance/:uuid` | u | 更新保险信息 |
| **Patient Message** | POST | `/api/patient/:pid/message` | c | 发送患者消息 |
| | PUT | `/api/patient/:pid/message/:mid` | u | 更新消息 |
| | DELETE | `/api/patient/:pid/message/:mid` | d | 删除消息 |
| **Transaction (Referral)** | GET | `/api/patient/:pid/transaction` | rs | 获取转诊记录 |
| | POST | `/api/patient/:pid/transaction` | c | 创建转诊记录 |
| | PUT | `/api/transaction/:tid` | u | 更新转诊记录 |
| **Immunization** | GET | `/api/immunization` | rs | 获取免疫记录（只读） |
| | GET | `/api/immunization/:uuid` | r | 获取单个免疫记录（只读） |
| **Procedure** | GET | `/api/procedure` | rs | 获取程序记录（只读） |
| | GET | `/api/procedure/:uuid` | r | 获取单个程序记录（只读） |
| **Drug** | GET | `/api/drug` | rs | 获取药品列表（只读） |
| | GET | `/api/drug/:uuid` | r | 获取药品详情（只读） |
| **Prescription** | GET | `/api/prescription` | rs | 获取处方列表（只读） |
| | GET | `/api/prescription/:uuid` | r | 获取处方详情（只读） |
| **Version** | GET | `/api/version` | r | 获取 OpenEMR 版本信息 |
| **Product** | GET | `/api/product` | r | 获取产品注册信息 |

**权限说明：**
- `c` = Create（创建）
- `r` = Read（读取）
- `u` = Update（更新）
- `d` = Delete（删除）
- `s` = Search（搜索）

### Standard API 示例

#### 创建患者

```bash
curl -X POST 'https://localhost:9300/apis/default/api/patient' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/json' \
  --data '{
    "title": "Mr",
    "fname": "张",
    "lname": "三",
    "DOB": "1990-01-15",
    "sex": "Male",
    "street": "北京市朝阳区xxx",
    "city": "北京",
    "state": "北京",
    "postal_code": "100000",
    "phone_home": "13800138000",
    "email": "zhangsan@example.com"
  }'
```

#### 搜索患者

```bash
curl -X GET 'https://localhost:9300/apis/default/api/patient?lname=张&city=北京' \
  -H 'Authorization: Bearer YOUR_TOKEN'
```

#### 创建预约

```bash
curl -X POST 'https://localhost:9300/apis/default/api/patient/1/appointment' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/json' \
  --data '{
    "pc_catid": "5",
    "pc_title": "常规检查",
    "pc_duration": "1800",
    "pc_eventDate": "2024-02-15",
    "pc_startTime": "09:00:00",
    "pc_facility": "1",
    "pid": "1"
  }'
```

---

## FHIR API (FHIR R4)

**基础路径：** `https://your-host/apis/default/fhir`

FHIR API 遵循 HL7 FHIR R4 标准，提供标准化的医疗数据交换。

### 支持的 FHIR 资源

#### 管理资源（Administrative）

| 资源类型 | 操作 | 说明 |
|---------|------|------|
| **Patient** | CRUD + Search | 患者信息 |
| **Practitioner** | CRUD + Search | 医生/医护人员 |
| **PractitionerRole** | CRUD + Search | 医生角色 |
| **Organization** | CRUD + Search | 组织机构 |
| **Location** | CRUD + Search | 位置/设施 |
| **Person** | CRUD + Search | 人员信息 |
| **RelatedPerson** | CRUD + Search | 相关人员（家属等） |

#### 临床资源（Clinical）

| 资源类型 | 操作 | 说明 |
|---------|------|------|
| **Encounter** | CRUD + Search | 就诊记录 |
| **Observation** | CRUD + Search | 观察结果（生命体征、检验等） |
| **Condition** | CRUD + Search | 诊断/疾病 |
| **AllergyIntolerance** | CRUD + Search | 过敏信息 |
| **MedicationRequest** | CRUD + Search | 用药请求（处方） |
| **Medication** | Read + Search | 药品信息 |
| **MedicationDispense** | CRUD + Search | 药品发放 |
| **Procedure** | CRUD + Search | 医疗程序 |
| **Immunization** | CRUD + Search | 免疫接种 |
| **CarePlan** | CRUD + Search | 护理计划 |
| **CareTeam** | CRUD + Search | 护理团队 |
| **Goal** | CRUD + Search | 治疗目标 |
| **ServiceRequest** | CRUD + Search | 服务请求（检验、检查） |
| **DiagnosticReport** | CRUD + Search | 诊断报告 |
| **Specimen** | CRUD + Search | 标本 |

#### 文档资源（Document）

| 资源类型 | 操作 | 说明 |
|---------|------|------|
| **DocumentReference** | CRUD + Search | 文档引用 |
| **Media** | CRUD + Search | 媒体文件（图片、视频） |

#### 其他资源

| 资源类型 | 操作 | 说明 |
|---------|------|------|
| **Appointment** | CRUD + Search | 预约 |
| **Coverage** | CRUD + Search | 保险覆盖 |
| **Device** | CRUD + Search | 医疗设备 |
| **Provenance** | Read + Search | 数据来源追踪 |
| **Questionnaire** | CRUD + Search | 问卷 |
| **QuestionnaireResponse** | CRUD + Search | 问卷回答 |
| **ValueSet** | Read + Search | 值集（术语） |
| **Group** | CRUD + Search | 分组（用于批量导出） |

### FHIR API 操作

#### 标准 CRUD 操作

```http
# 创建资源
POST /fhir/Patient
Content-Type: application/fhir+json
Authorization: Bearer TOKEN

# 读取资源
GET /fhir/Patient/{id}

# 更新资源
PUT /fhir/Patient/{id}

# 删除资源
DELETE /fhir/Patient/{id}

# 搜索资源
GET /fhir/Patient?name=张&birthdate=1990
```

#### 特殊操作

| 操作 | 路径 | 说明 |
|------|------|------|
| **Capability Statement** | `GET /fhir/metadata` | 获取服务器能力声明（无需认证） |
| **$docref** | `POST /fhir/DocumentReference/$docref` | 生成 CCD-A 文档 |
| **$export** | `GET /fhir/Patient/$export` | 批量导出患者数据 |
| **$export** | `GET /fhir/Group/{id}/$export` | 批量导出分组数据 |
| **$export** | `GET /fhir/$export` | 系统级批量导出 |

### FHIR API 示例

#### 创建患者（FHIR 格式）

```bash
curl -X POST 'https://localhost:9300/apis/default/fhir/Patient' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/fhir+json' \
  --data '{
    "resourceType": "Patient",
    "name": [{
      "family": "张",
      "given": ["三"]
    }],
    "birthDate": "1990-01-15",
    "gender": "male",
    "telecom": [{
      "system": "phone",
      "value": "13800138000"
    }],
    "address": [{
      "city": "北京",
      "postalCode": "100000"
    }]
  }'
```

#### 搜索观察结果

```bash
curl -X GET 'https://localhost:9300/apis/default/fhir/Observation?patient=Patient/123&category=vital-signs' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Accept: application/fhir+json'
```

#### 创建预约

```bash
curl -X POST 'https://localhost:9300/apis/default/fhir/Appointment' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/fhir+json' \
  --data '{
    "resourceType": "Appointment",
    "status": "proposed",
    "serviceType": [{
      "coding": [{
        "system": "http://terminology.hl7.org/CodeSystem/service-type",
        "code": "general"
      }]
    }],
    "start": "2024-02-15T09:00:00Z",
    "end": "2024-02-15T09:30:00Z",
    "participant": [{
      "actor": {
        "reference": "Patient/123"
      },
      "status": "accepted"
    }]
  }'
```

---

## Patient Portal API

**基础路径：** `https://your-host/apis/default/portal`

⚠️ **注意：** Patient Portal API 目前为实验性功能，功能有限。

### 端点列表

| 资源 | 方法 | 路径 | 说明 |
|------|------|------|------|
| **Patient** | GET | `/portal/patient` | 获取当前登录患者信息 |
| **Encounter** | GET | `/portal/patient/encounter` | 获取患者就诊记录列表 |
| | GET | `/portal/patient/encounter/:euuid` | 获取单个就诊记录 |
| **Appointment** | GET | `/portal/patient/appointment` | 获取患者预约列表 |
| | GET | `/portal/patient/appointment/:auuid` | 获取单个预约 |

### 认证要求

患者必须通过医生生成 API 凭证：
1. 进入患者档案
2. 点击 **API Credentials** 按钮
3. 生成患者凭证

患者使用 **Password Grant** 方式认证（`user_role=patient`）。

### Patient Portal API 示例

```bash
# 获取患者信息
curl -X GET 'https://localhost:9300/apis/default/portal/patient' \
  -H 'Authorization: Bearer PATIENT_TOKEN'

# 获取预约列表
curl -X GET 'https://localhost:9300/apis/default/portal/patient/appointment' \
  -H 'Authorization: Bearer PATIENT_TOKEN'
```

---

## 小程序接入建议

### 1. API 选择建议

| 场景 | 推荐 API | 原因 |
|------|---------|------|
| **患者端小程序** | FHIR API + Patient Portal API | 标准化、互操作性好 |
| **医生端小程序** | FHIR API | 标准医疗数据模型 |
| **管理功能** | Standard API | 原生数据结构，功能完整 |
| **数据同步** | FHIR API | 符合医疗行业标准 |

### 2. 认证流程（小程序）

#### 方案一：Authorization Code Grant（推荐）

```javascript
// 1. 引导用户到授权页面
const authUrl = `https://your-openemr-host/oauth2/default/authorize?
  client_id=${CLIENT_ID}&
  redirect_uri=${encodeURIComponent(REDIRECT_URI)}&
  response_type=code&
  scope=openid offline_access api:fhir patient/Patient.rs patient/Observation.rs&
  state=${RANDOM_STATE}`;

// 小程序中打开授权页面
wx.navigateTo({ url: authUrl });

// 2. 在回调页面获取授权码
// 3. 后端交换访问令牌（避免在小程序中暴露 client_secret）
```

#### 方案二：后端代理认证（更安全）

```javascript
// 小程序 → 后端服务器 → OpenEMR
// 后端服务器负责 OAuth2 流程，小程序只调用后端 API
```

### 3. 权限范围（Scopes）建议

#### 患者端小程序

```
openid 
offline_access 
api:fhir 
patient/Patient.rs 
patient/Observation.rs 
patient/Condition.rs 
patient/MedicationRequest.rs 
patient/Appointment.rs 
patient/Encounter.rs 
patient/DocumentReference.rs
```

#### 医生端小程序

```
openid 
offline_access 
api:fhir 
user/Patient.rs 
user/Observation.cruds 
user/Condition.cruds 
user/MedicationRequest.cruds 
user/Appointment.cruds 
user/Encounter.cruds
```

### 4. 错误处理

```javascript
// 统一错误处理
function handleApiError(error) {
  if (error.status === 401) {
    // Token 过期，重新获取
    refreshToken();
  } else if (error.status === 403) {
    // 权限不足
    wx.showToast({ title: '权限不足', icon: 'none' });
  } else if (error.status === 404) {
    // 资源不存在
    wx.showToast({ title: '资源不存在', icon: 'none' });
  } else {
    // 其他错误
    wx.showToast({ title: '请求失败', icon: 'none' });
  }
}
```

### 5. Token 管理

```javascript
// 存储 Token
wx.setStorageSync('access_token', token);
wx.setStorageSync('refresh_token', refreshToken);
wx.setStorageSync('token_expires_at', expiresAt);

// 检查 Token 是否过期
function isTokenExpired() {
  const expiresAt = wx.getStorageSync('token_expires_at');
  return Date.now() >= expiresAt * 1000;
}

// 刷新 Token
async function refreshToken() {
  const refreshToken = wx.getStorageSync('refresh_token');
  // 调用刷新接口
  // ...
}
```

### 6. 请求封装示例

```javascript
// api.js
const BASE_URL = 'https://your-openemr-host/apis/default';

function request(url, method = 'GET', data = null) {
  return new Promise((resolve, reject) => {
    const token = wx.getStorageSync('access_token');
    
    // 检查 Token 是否过期
    if (isTokenExpired()) {
      refreshToken().then(() => {
        makeRequest();
      }).catch(reject);
    } else {
      makeRequest();
    }
    
    function makeRequest() {
      wx.request({
        url: BASE_URL + url,
        method: method,
        data: data,
        header: {
          'Authorization': `Bearer ${wx.getStorageSync('access_token')}`,
          'Content-Type': 'application/json',
          'Accept': 'application/fhir+json'
        },
        success: (res) => {
          if (res.statusCode >= 200 && res.statusCode < 300) {
            resolve(res.data);
          } else {
            handleApiError(res);
            reject(res);
          }
        },
        fail: reject
      });
    }
  });
}

// 使用示例
export const api = {
  // 获取患者信息
  getPatient: (patientId) => request(`/fhir/Patient/${patientId}`, 'GET'),
  
  // 搜索患者
  searchPatients: (params) => request(`/fhir/Patient?${buildQuery(params)}`, 'GET'),
  
  // 获取预约列表
  getAppointments: (patientId) => request(`/fhir/Appointment?patient=Patient/${patientId}`, 'GET'),
  
  // 创建预约
  createAppointment: (data) => request('/fhir/Appointment', 'POST', data),
  
  // 获取就诊记录
  getEncounters: (patientId) => request(`/fhir/Encounter?patient=Patient/${patientId}`, 'GET'),
  
  // 获取观察结果（生命体征、检验等）
  getObservations: (patientId, category) => {
    const params = `patient=Patient/${patientId}${category ? '&category=' + category : ''}`;
    return request(`/fhir/Observation?${params}`, 'GET');
  }
};
```

---

## 快速开始

### 步骤 1: 启用 API

1. 登录 OpenEMR 管理后台
2. 进入 **Administration → Config → Connectors**
3. 启用：
   - ☑ Enable OpenEMR Standard REST API
   - ☑ Enable OpenEMR Standard FHIR REST API
4. 设置 **Site Address**: `https://your-openemr-host`

### 步骤 2: 注册客户端

```bash
curl -X POST -k -H 'Content-Type: application/json' \
  https://your-openemr-host/oauth2/default/registration \
  --data '{
    "application_type": "public",
    "redirect_uris": ["https://your-miniprogram-callback"],
    "client_name": "小程序应用",
    "token_endpoint_auth_method": "none",
    "scope": "openid offline_access api:fhir patient/Patient.rs patient/Observation.rs patient/Appointment.rs"
  }'
```

保存返回的 `client_id`。

### 步骤 3: 测试 API

```bash
# 获取 Capability Statement（无需认证）
curl -X GET 'https://your-openemr-host/apis/default/fhir/metadata' \
  -H 'Accept: application/fhir+json'
```

### 步骤 4: 在小程序中集成

参考上面的"小程序接入建议"部分，实现认证和 API 调用。

---

## 参考资源

- **官方文档：** `/Documentation/api/`
- **Swagger UI：** `https://your-openemr-host/swagger/`
- **在线演示：** https://www.open-emr.org/wiki/index.php/Development_Demo
- **社区论坛：** https://community.open-emr.org/
- **GitHub Issues：** https://github.com/openemr/openemr/issues

---

## 注意事项

1. **SSL/TLS 必需**：所有 API 请求必须使用 HTTPS
2. **Token 安全**：不要在前端代码中硬编码 Token，使用安全的存储方式
3. **权限最小化**：只申请必要的权限范围（Scopes）
4. **错误处理**：实现完善的错误处理和 Token 刷新机制
5. **数据隐私**：遵循 HIPAA 等医疗数据隐私法规
6. **Patient Portal API**：目前为实验性功能，功能有限，建议优先使用 FHIR API

---

**最后更新：** 2024年
**OpenEMR 版本：** 7.0.4+
