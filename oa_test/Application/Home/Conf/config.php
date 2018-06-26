<?php
return array(
    'TMPL_PARSE_STRING' => array(
        '__IMG__'    => __ROOT__ . '/' . APP_NAME . '/' . MODULE_NAME . '/Public/images', //图片目录
        '__CSS__'    => __ROOT__ . '/' . APP_NAME . '/' . MODULE_NAME . '/Public/css', //CSS目录
        '__JS__'     => __ROOT__ . '/' . APP_NAME . '/' . MODULE_NAME . '/Public/js', //JS目录
        '__TMPATH__' => __ROOT__ . '/' . APP_NAME . '/' . MODULE_NAME . '/View', //JS目录
    ),
	'PAY_METHOD' =>array(
		1=>'银行转账',
		2=>'现金',
		/* 3=>'支付宝', */
		3=>'其它',
	),
    'MAIL_HOST' =>'smtp.maxpr.com.cn',//smtp服务器的名称
    'MAIL_SMTPAUTH' =>TRUE, //启用smtp认证
    'MAIL_USERNAME' =>'oa.shanghai@maxpr.com.cn',//发件人的邮箱名
    'MAIL_PASSWORD' =>'xinnet123A',//163邮箱发件人授权密码
    'MAIL_FROM' =>'oa.shanghai@maxpr.com.cn',//发件人邮箱地址
    'MAIL_FROMNAME'=>'oa.shanghai',//发件人姓名
    'MAIL_CHARSET' =>'utf-8',//设置邮件编码
    'MAIL_ISHTML' =>TRUE, // 是否HTML格式邮件
    /* 'IMG_DOMAIN' => 'http://oa.max-digital.cn/Upload',
    'UPLOAD_DOMAIN' => '/www/oa/Upload', */
    'IMG_DOMAIN' => 'http://pic.max.dev',
    'UPLOAD_DOMAIN' => 'D:\maxpr_tmp\uploads ',
	//Litter_7 短信发送参数配置
    'SMS_ALI_CONFIG' => array(
        'app_key' => '24537034',
        'app_secret' => 'b7e78f96c164083332aa87c828945deb',
		'sign_name'  => 'MAX智源动力'
    ),
    'SMS_SHOWAPI_CONFIG' => array(
        'app_appid' => '42179',
        'app_secret' => 'b069ceff0e1e41bbbc3a774177536e35',
        'request_url' => 'http://route.showapi.com/28-1',
    ),
    'RESIGN_PERSON_RESON' => array(
        '找到更好的工作(Better Offer)',
        '自己经营生意(Start Own Business)',
        '移民(Emigration)',
        '回校深造(Further Education)',
        '健康原因(Health issue)',
        '家庭原因(Family reason)',
        '对这个行业没太多热情，想转去其他行业(Changing profession)'
    ),
    'RESIGN_COMPANY_RESON' => array(
        '对工资或 福利不满意(Compensation & Benefits）',
        '工作环境(Working Environment)',
        '不满意公司的政策和措施(Company Policy & Regulations)',
        '没有事业发展机会(Limited Career development)',
        '工作太枯燥, 缺少热情(Lack of Job Challenge)',
        '缺少学习和知识(Lack of Training)',
        '工作压力太大，个人不能承受(Workload & OT)',
        '同事关系不融洽(Relationship with Colleagues)',
        '与上司关系不融洽(Relationship with Line Manager)',
        '公司内部产品没有创新，产品使用极不便捷(Product Innovation Problem)'
    ),
    'RESIGN_LIMIT_ITEM' => array(
        '公司服务1年半以上（包括1年半），担任核心关键岗位；Service MAX more than 1 and a half years as a key membe',
        '掌握丰富的公司知识（包括作为知识宣传培训人，内部协调人）；Master the company’s  intelligence',
        '掌握公司丰富的客户资源和产品资源；Master a range of client and products resources'
    )
);