一、患者端（Patient Portal）
访问地址：http://localhost:8300/portal 或 https://localhost:9300/portal
主要功能模块：
仪表板（Dashboard）
查看健康概览
快速访问常用功能
预约管理（Appointments）
查看未来和过去的预约
在线预约
查看预约详情（日期、时间、医生、类型、状态）
安全消息（Secure Messaging）
与医生/诊所沟通
接收消息通知
查看消息历史
表单和文档（Forms and Documents）
填写在线表单
查看和下载医疗文档
上传文件
个人资料（Profile）
查看和更新个人信息
管理联系方式
更新保险信息
健康快照（Health Snapshot）
查看诊断问题（Problems）
查看药物清单（Medications）
查看过敏信息（Allergies）
查看生命体征（Vitals）
查看实验室结果（Lab Results）
查看处方（Prescriptions）
账单摘要（Billing Summary）
查看账单明细
查看付款历史
在线支付（如果启用）
医疗报告（Medical Reports）
查看和下载医疗报告
查看自定义报告
患者报告结果（PRO Assessment）
完成患者报告结果评估
设置（Settings）
账户设置
通知偏好
患者登录方式：
需要医生/管理员创建患者门户账户
登录后访问个人健康信息
二、医生/临床人员端（Clinician Interface）
访问地址：http://localhost:8300 或 https://localhost:9300
默认登录：用户名 admin，密码 pass
主要功能模块：
患者档案（Patient File）
仪表板：患者概览、人口统计、保险信息
历史记录：医疗历史、问题列表、药物、过敏
评估：SDOH 评估等
报告：生成患者报告
文档：管理医疗文档
交易：转诊、授权等
预约管理（Calendar/Appointments）
查看和管理预约
创建新预约
预约状态管理
重复预约设置
就诊记录（Encounters）
创建和管理就诊记录
临床笔记
生命体征录入
诊断编码
检查结果
临床决策支持（Clinical Decision Rules）
临床提醒和警报
质量指标跟踪
自动临床决策支持
处方管理（Prescriptions）
开具电子处方
查看处方历史
药物相互作用检查
实验室结果（Lab Results）
查看实验室结果
签署结果
结果管理
文档管理（Documents）
上传和管理文档
文档模板
文档分类
报告和统计（Reports）
患者报告
财务报告
临床质量报告
标准措施（CQM）
自动化措施（AMC）
账单（Billing）
费用表管理
保险索赔
EOB 数据录入
财务报告
管理功能（Administration）
用户管理
系统配置
实践设置
数据库管理
模块管理
ACL（访问控制列表）管理
仪表板上下文管理（Dashboard Context Manager）
根据护理场景切换视图：
初级护理（Primary Care）
门诊（Outpatient）
住院（Inpatient）
急诊（Emergency）
专科（Specialty）
远程医疗（Telehealth）
行为健康（Behavioral Health）
儿科（Pediatric）
老年科（Geriatric）
三、系统特色功能
FHIR API 支持
符合 FHIR R4 标准
支持 US Core 8.0
SMART on FHIR v2.2.0 认证
互操作性
CCD-A 文档生成
批量数据导出
标准 API 接口
安全性
细粒度访问控制（ACL）
审计日志
数据加密
多语言支持
国际化界面
多语言文档
四、使用流程示例
患者端流程：
注册/登录患者门户
查看预约和消息
填写在线表单
查看健康信息
与医生沟通
支付账单
医生端流程：
登录系统
查看患者列表或搜索患者
打开患者档案
查看患者历史信息
创建就诊记录
录入临床笔记和诊断
开具处方
查看实验室结果
生成报告
管理预约

五、快速开始建议
首次使用：
以管理员身份登录
创建测试患者
为患者创建门户账户
测试患者门户功能
熟悉系统：
探索患者档案功能
创建测试预约
录入测试就诊记录
查看各种报告
配置系统：
设置实践信息
配置用户权限
设置临床提醒
配置账单设置
如需更详细的某个功能说明，请告知。


