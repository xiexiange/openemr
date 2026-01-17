# OpenEMR API 端点速查表

快速查阅所有可用的 API 端点。

## 基础路径

```
Standard API:  https://your-host/apis/default/api
FHIR API:      https://your-host/apis/default/fhir
Portal API:    https://your-host/apis/default/portal
OAuth2:        https://your-host/oauth2/default
```

---

## Standard API 端点

### Facility（设施）
```
GET    /api/facility                    # 列表
GET    /api/facility/:fuuid            # 详情
POST   /api/facility                   # 创建
PUT    /api/facility/:fuuid            # 更新
```

### Patient（患者）
```
GET    /api/patient                    # 搜索
GET    /api/patient/:puuid            # 详情
POST   /api/patient                   # 创建
PUT    /api/patient/:puuid            # 更新
```

### Encounter（就诊记录）
```
GET    /api/patient/:puuid/encounter              # 列表
GET    /api/patient/:puuid/encounter/:euuid      # 详情
POST   /api/patient/:puuid/encounter             # 创建
PUT    /api/patient/:puuid/encounter/:euuid      # 更新
```

### SOAP Note（SOAP 笔记）
```
GET    /api/patient/:pid/encounter/:eid/soap_note           # 列表
GET    /api/patient/:pid/encounter/:eid/soap_note/:sid     # 详情
POST   /api/patient/:pid/encounter/:eid/soap_note          # 创建
PUT    /api/patient/:pid/encounter/:eid/soap_note/:sid     # 更新
```

### Vitals（生命体征）
```
GET    /api/patient/:pid/encounter/:eid/vital        # 列表
GET    /api/patient/:pid/encounter/:eid/vital/:vid  # 详情
POST   /api/patient/:pid/encounter/:eid/vital       # 创建
PUT    /api/patient/:pid/encounter/:eid/vital/:vid  # 更新
```

### Practitioner（医生）
```
GET    /api/practitioner              # 列表
GET    /api/practitioner/:pruuid    # 详情
POST   /api/practitioner            # 创建
PUT    /api/practitioner/:pruuid    # 更新
```

### Medical Problem（医疗问题）
```
GET    /api/medical_problem                          # 列表
GET    /api/patient/:puuid/medical_problem           # 患者列表
GET    /api/patient/:puuid/medical_problem/:muuid    # 详情
POST   /api/patient/:puuid/medical_problem           # 创建
PUT    /api/patient/:puuid/medical_problem/:muuid    # 更新
DELETE /api/patient/:puuid/medical_problem/:muuid    # 删除
```

### Allergy（过敏）
```
GET    /api/allergy                          # 列表
GET    /api/patient/:puuid/allergy           # 患者列表
GET    /api/patient/:puuid/allergy/:auuid    # 详情
POST   /api/patient/:puuid/allergy           # 创建
PUT    /api/patient/:puuid/allergy/:auuid    # 更新
DELETE /api/patient/:puuid/allergy/:auuid    # 删除
```

### Medication（用药）
```
GET    /api/patient/:pid/medication          # 列表
GET    /api/patient/:pid/medication/:mid     # 详情
POST   /api/patient/:pid/medication         # 创建
PUT    /api/patient/:pid/medication/:mid     # 更新
DELETE /api/patient/:pid/medication/:mid    # 删除
```

### Surgery（手术）
```
GET    /api/patient/:pid/surgery             # 列表
GET    /api/patient/:pid/surgery/:sid        # 详情
POST   /api/patient/:pid/surgery            # 创建
PUT    /api/patient/:pid/surgery/:sid        # 更新
DELETE /api/patient/:pid/surgery/:sid       # 删除
```

### Dental Issue（牙科问题）
```
GET    /api/patient/:pid/dental_issue            # 列表
GET    /api/patient/:pid/dental_issue/:did      # 详情
POST   /api/patient/:pid/dental_issue           # 创建
PUT    /api/patient/:pid/dental_issue/:did       # 更新
DELETE /api/patient/:pid/dental_issue/:did       # 删除
```

### Appointment（预约）
```
GET    /api/appointment                      # 列表
GET    /api/appointment/:eid                 # 详情
GET    /api/patient/:pid/appointment         # 患者列表
GET    /api/patient/:pid/appointment/:eid    # 患者详情
POST   /api/patient/:pid/appointment         # 创建
DELETE /api/patient/:pid/appointment/:eid    # 删除
```

### List（列表数据，只读）
```
GET    /api/list/:list_name    # 获取列表数据
```

### User（用户，只读）
```
GET    /api/user           # 列表
GET    /api/user/:uuid     # 详情
```

### Insurance Company（保险公司）
```
GET    /api/insurance_company           # 列表
GET    /api/insurance_company/:iid      # 详情
POST   /api/insurance_company           # 创建
PUT    /api/insurance_company/:iid      # 更新
```

### Insurance Type（保险类型，只读）
```
GET    /api/insurance_type    # 列表
```

### Patient Document（患者文档）
```
GET    /api/patient/:pid/document           # 列表
GET    /api/patient/:pid/document/:did     # 下载
POST   /api/patient/:pid/document          # 上传
```

### Patient Employer（患者雇主，只读）
```
GET    /api/patient/:puuid/employer    # 列表
```

### Patient Insurance（患者保险）
```
GET    /api/patient/:puuid/insurance              # 列表
GET    /api/patient/:puuid/insurance/:uuid        # 详情
POST   /api/patient/:puuid/insurance               # 创建
PUT    /api/patient/:puuid/insurance/:uuid        # 更新
```

### Patient Message（患者消息）
```
POST   /api/patient/:pid/message           # 发送
PUT    /api/patient/:pid/message/:mid      # 更新
DELETE /api/patient/:pid/message/:mid      # 删除
```

### Transaction/Referral（转诊）
```
GET    /api/patient/:pid/transaction    # 列表
POST   /api/patient/:pid/transaction    # 创建
PUT    /api/transaction/:tid             # 更新
```

### Immunization（免疫，只读）
```
GET    /api/immunization           # 列表
GET    /api/immunization/:uuid     # 详情
```

### Procedure（程序，只读）
```
GET    /api/procedure           # 列表
GET    /api/procedure/:uuid     # 详情
```

### Drug（药品，只读）
```
GET    /api/drug           # 列表
GET    /api/drug/:uuid     # 详情
```

### Prescription（处方，只读）
```
GET    /api/prescription           # 列表
GET    /api/prescription/:uuid     # 详情
```

### Version（版本信息）
```
GET    /api/version    # OpenEMR 版本
```

### Product（产品信息）
```
GET    /api/product    # 产品注册信息
```

---

## FHIR API 端点

### 管理资源（Administrative）

#### Patient（患者）
```
GET    /fhir/Patient              # 搜索
GET    /fhir/Patient/:id          # 读取
POST   /fhir/Patient              # 创建
PUT    /fhir/Patient/:id          # 更新
DELETE /fhir/Patient/:id          # 删除
```

#### Practitioner（医生）
```
GET    /fhir/Practitioner         # 搜索
GET    /fhir/Practitioner/:id     # 读取
POST   /fhir/Practitioner         # 创建
PUT    /fhir/Practitioner/:id     # 更新
DELETE /fhir/Practitioner/:id     # 删除
```

#### PractitionerRole（医生角色）
```
GET    /fhir/PractitionerRole     # 搜索
GET    /fhir/PractitionerRole/:id # 读取
POST   /fhir/PractitionerRole     # 创建
PUT    /fhir/PractitionerRole/:id # 更新
DELETE /fhir/PractitionerRole/:id # 删除
```

#### Organization（组织机构）
```
GET    /fhir/Organization         # 搜索
GET    /fhir/Organization/:id     # 读取
POST   /fhir/Organization         # 创建
PUT    /fhir/Organization/:id     # 更新
DELETE /fhir/Organization/:id     # 删除
```

#### Location（位置）
```
GET    /fhir/Location             # 搜索
GET    /fhir/Location/:id         # 读取
POST   /fhir/Location             # 创建
PUT    /fhir/Location/:id         # 更新
DELETE /fhir/Location/:id         # 删除
```

#### Person（人员）
```
GET    /fhir/Person               # 搜索
GET    /fhir/Person/:id           # 读取
POST   /fhir/Person               # 创建
PUT    /fhir/Person/:id           # 更新
DELETE /fhir/Person/:id           # 删除
```

#### RelatedPerson（相关人员）
```
GET    /fhir/RelatedPerson        # 搜索
GET    /fhir/RelatedPerson/:id   # 读取
POST   /fhir/RelatedPerson       # 创建
PUT    /fhir/RelatedPerson/:id   # 更新
DELETE /fhir/RelatedPerson/:id   # 删除
```

### 临床资源（Clinical）

#### Encounter（就诊记录）
```
GET    /fhir/Encounter            # 搜索
GET    /fhir/Encounter/:id        # 读取
POST   /fhir/Encounter            # 创建
PUT    /fhir/Encounter/:id        # 更新
DELETE /fhir/Encounter/:id        # 删除
```

#### Observation（观察结果）
```
GET    /fhir/Observation          # 搜索
GET    /fhir/Observation/:id       # 读取
POST   /fhir/Observation          # 创建
PUT    /fhir/Observation/:id      # 更新
DELETE /fhir/Observation/:id      # 删除
```

#### Condition（诊断）
```
GET    /fhir/Condition             # 搜索
GET    /fhir/Condition/:id        # 读取
POST   /fhir/Condition            # 创建
PUT    /fhir/Condition/:id        # 更新
DELETE /fhir/Condition/:id        # 删除
```

#### AllergyIntolerance（过敏）
```
GET    /fhir/AllergyIntolerance    # 搜索
GET    /fhir/AllergyIntolerance/:id # 读取
POST   /fhir/AllergyIntolerance    # 创建
PUT    /fhir/AllergyIntolerance/:id # 更新
DELETE /fhir/AllergyIntolerance/:id # 删除
```

#### MedicationRequest（用药请求/处方）
```
GET    /fhir/MedicationRequest     # 搜索
GET    /fhir/MedicationRequest/:id # 读取
POST   /fhir/MedicationRequest    # 创建
PUT    /fhir/MedicationRequest/:id # 更新
DELETE /fhir/MedicationRequest/:id # 删除
```

#### Medication（药品）
```
GET    /fhir/Medication            # 搜索
GET    /fhir/Medication/:id       # 读取
```

#### MedicationDispense（药品发放）
```
GET    /fhir/MedicationDispense    # 搜索
GET    /fhir/MedicationDispense/:id # 读取
POST   /fhir/MedicationDispense    # 创建
PUT    /fhir/MedicationDispense/:id # 更新
DELETE /fhir/MedicationDispense/:id # 删除
```

#### Procedure（医疗程序）
```
GET    /fhir/Procedure             # 搜索
GET    /fhir/Procedure/:id        # 读取
POST   /fhir/Procedure            # 创建
PUT    /fhir/Procedure/:id        # 更新
DELETE /fhir/Procedure/:id        # 删除
```

#### Immunization（免疫接种）
```
GET    /fhir/Immunization          # 搜索
GET    /fhir/Immunization/:id     # 读取
POST   /fhir/Immunization          # 创建
PUT    /fhir/Immunization/:id     # 更新
DELETE /fhir/Immunization/:id     # 删除
```

#### CarePlan（护理计划）
```
GET    /fhir/CarePlan              # 搜索
GET    /fhir/CarePlan/:id          # 读取
POST   /fhir/CarePlan              # 创建
PUT    /fhir/CarePlan/:id         # 更新
DELETE /fhir/CarePlan/:id         # 删除
```

#### CareTeam（护理团队）
```
GET    /fhir/CareTeam              # 搜索
GET    /fhir/CareTeam/:id         # 读取
POST   /fhir/CareTeam             # 创建
PUT    /fhir/CareTeam/:id         # 更新
DELETE /fhir/CareTeam/:id         # 删除
```

#### Goal（治疗目标）
```
GET    /fhir/Goal                  # 搜索
GET    /fhir/Goal/:id              # 读取
POST   /fhir/Goal                  # 创建
PUT    /fhir/Goal/:id              # 更新
DELETE /fhir/Goal/:id              # 删除
```

#### ServiceRequest（服务请求）
```
GET    /fhir/ServiceRequest         # 搜索
GET    /fhir/ServiceRequest/:id    # 读取
POST   /fhir/ServiceRequest        # 创建
PUT    /fhir/ServiceRequest/:id    # 更新
DELETE /fhir/ServiceRequest/:id    # 删除
```

#### DiagnosticReport（诊断报告）
```
GET    /fhir/DiagnosticReport       # 搜索
GET    /fhir/DiagnosticReport/:id   # 读取
POST   /fhir/DiagnosticReport      # 创建
PUT    /fhir/DiagnosticReport/:id  # 更新
DELETE /fhir/DiagnosticReport/:id  # 删除
```

#### Specimen（标本）
```
GET    /fhir/Specimen               # 搜索
GET    /fhir/Specimen/:id          # 读取
POST   /fhir/Specimen               # 创建
PUT    /fhir/Specimen/:id          # 更新
DELETE /fhir/Specimen/:id          # 删除
```

### 文档资源（Document）

#### DocumentReference（文档引用）
```
GET    /fhir/DocumentReference     # 搜索
GET    /fhir/DocumentReference/:id # 读取
POST   /fhir/DocumentReference     # 创建
PUT    /fhir/DocumentReference/:id # 更新
DELETE /fhir/DocumentReference/:id # 删除
POST   /fhir/DocumentReference/$docref  # 生成 CCD-A
```

#### Media（媒体文件）
```
GET    /fhir/Media                  # 搜索
GET    /fhir/Media/:id             # 读取
POST   /fhir/Media                 # 创建
PUT    /fhir/Media/:id             # 更新
DELETE /fhir/Media/:id             # 删除
```

### 其他资源

#### Appointment（预约）
```
GET    /fhir/Appointment            # 搜索
GET    /fhir/Appointment/:id       # 读取
POST   /fhir/Appointment           # 创建
PUT    /fhir/Appointment/:id       # 更新
DELETE /fhir/Appointment/:id       # 删除
```

#### Coverage（保险覆盖）
```
GET    /fhir/Coverage               # 搜索
GET    /fhir/Coverage/:id          # 读取
POST   /fhir/Coverage               # 创建
PUT    /fhir/Coverage/:id          # 更新
DELETE /fhir/Coverage/:id          # 删除
```

#### Device（医疗设备）
```
GET    /fhir/Device                 # 搜索
GET    /fhir/Device/:id            # 读取
POST   /fhir/Device                 # 创建
PUT    /fhir/Device/:id            # 更新
DELETE /fhir/Device/:id            # 删除
```

#### Provenance（数据来源）
```
GET    /fhir/Provenance             # 搜索
GET    /fhir/Provenance/:id        # 读取
```

#### Questionnaire（问卷）
```
GET    /fhir/Questionnaire          # 搜索
GET    /fhir/Questionnaire/:id     # 读取
POST   /fhir/Questionnaire         # 创建
PUT    /fhir/Questionnaire/:id     # 更新
DELETE /fhir/Questionnaire/:id    # 删除
```

#### QuestionnaireResponse（问卷回答）
```
GET    /fhir/QuestionnaireResponse  # 搜索
GET    /fhir/QuestionnaireResponse/:id # 读取
POST   /fhir/QuestionnaireResponse  # 创建
PUT    /fhir/QuestionnaireResponse/:id # 更新
DELETE /fhir/QuestionnaireResponse/:id # 删除
```

#### ValueSet（值集）
```
GET    /fhir/ValueSet               # 搜索
GET    /fhir/ValueSet/:id          # 读取
```

#### Group（分组）
```
GET    /fhir/Group                  # 搜索
GET    /fhir/Group/:id             # 读取
POST   /fhir/Group                  # 创建
PUT    /fhir/Group/:id             # 更新
DELETE /fhir/Group/:id             # 删除
GET    /fhir/Group/:id/$export     # 批量导出
```

### FHIR 特殊操作

#### Capability Statement（能力声明，无需认证）
```
GET    /fhir/metadata    # 获取服务器能力
```

#### 批量导出操作
```
GET    /fhir/Patient/$export        # 患者批量导出
GET    /fhir/Group/:id/$export      # 分组批量导出
GET    /fhir/$export                # 系统级批量导出
```

---

## Patient Portal API 端点

⚠️ **实验性功能**

```
GET    /portal/patient                          # 当前患者信息
GET    /portal/patient/encounter                # 就诊记录列表
GET    /portal/patient/encounter/:euuid         # 单个就诊记录
GET    /portal/patient/appointment              # 预约列表
GET    /portal/patient/appointment/:auuid       # 单个预约
```

---

## OAuth2 端点

### 客户端注册
```
POST   /oauth2/default/registration    # 注册客户端
GET    /oauth2/default/client/:id      # 获取客户端信息
PUT    /oauth2/default/client/:id      # 更新客户端
DELETE /oauth2/default/client/:id      # 删除客户端
```

### 授权
```
GET    /oauth2/default/authorize       # 授权页面
POST   /oauth2/default/authorize      # POST 方式授权
```

### Token
```
POST   /oauth2/default/token          # 获取/刷新 Token
POST   /oauth2/default/introspect     # Token 验证
POST   /oauth2/default/revoke         # 撤销 Token
```

### OpenID Connect
```
GET    /oauth2/default/.well-known/openid-configuration    # OIDC 配置
GET    /oauth2/default/.well-known/smart-configuration     # SMART 配置
GET    /oauth2/default/userinfo                           # 用户信息
```

### 登出
```
GET    /oauth2/default/logout         # 登出
```

---

## 常用搜索参数示例

### FHIR 搜索示例

```bash
# 按患者搜索
GET /fhir/Observation?patient=Patient/123

# 按日期范围搜索
GET /fhir/Observation?patient=Patient/123&date=ge2024-01-01&date=le2024-12-31

# 按类别搜索
GET /fhir/Observation?patient=Patient/123&category=vital-signs

# 按类型搜索
GET /fhir/Observation?patient=Patient/123&code=http://loinc.org|85354-9

# 包含相关资源
GET /fhir/Encounter?patient=Patient/123&_include=Encounter:participant

# 排序和分页
GET /fhir/Observation?patient=Patient/123&_sort=-date&_count=10&_offset=0
```

### Standard API 搜索示例

```bash
# 搜索患者
GET /api/patient?lname=张&city=北京

# 搜索预约
GET /api/appointment?pc_eventDate=2024-02-15

# 搜索医生
GET /api/practitioner?fname=李
```

---

## HTTP 状态码

| 状态码 | 含义 | 说明 |
|--------|------|------|
| 200 | OK | 成功（GET/PUT/PATCH） |
| 201 | Created | 成功创建（POST） |
| 204 | No Content | 成功删除 |
| 400 | Bad Request | 请求格式错误 |
| 401 | Unauthorized | 未认证或 Token 无效 |
| 403 | Forbidden | 权限不足 |
| 404 | Not Found | 资源不存在 |
| 422 | Unprocessable Entity | 验证错误 |
| 500 | Internal Server Error | 服务器错误 |

---

## 请求头

### 必需
```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

### 推荐
```
Content-Type: application/json          # Standard API
Content-Type: application/fhir+json     # FHIR API
Accept: application/fhir+json          # FHIR API
```

---

**快速参考版本：** 2024
**完整文档：** 参见 `API_INTEGRATION_GUIDE.md`
