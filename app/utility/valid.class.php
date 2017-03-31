<?php
/**
 * 邮箱/手机号校验程序
 * @autho hetao
 */
class utility_valid{
	static $mobile_country = array(
		array("code"=>"CN", "mobile_pre"=>"+86", "name"=>"中国", "name_en"=>"China"),
		array("code"=>"TN", "mobile_pre"=>"+216", "name"=>"突尼斯", "name_en"=>"Tunisia"),
		array("code"=>"UZ", "mobile_pre"=>"+998", "name"=>"乌兹别克斯坦", "name_en"=>"Uzbekistan"),
		array("code"=>"KM", "mobile_pre"=>"+269", "name"=>"科摩罗", "name_en"=>"Comoros"),
		array("code"=>"PT", "mobile_pre"=>"+351", "name"=>"葡萄牙", "name_en"=>"Portugal"),
		array("code"=>"NE", "mobile_pre"=>"+227", "name"=>"尼日尔", "name_en"=>"Niger"),
		array("code"=>"BY", "mobile_pre"=>"+375", "name"=>"白俄罗斯", "name_en"=>"Belarus"),
		array("code"=>"DO", "mobile_pre"=>"+1809", "name"=>"多明尼加共和国", "name_en"=>"Dominican Republic"),
		array("code"=>"CL", "mobile_pre"=>"+56", "name"=>"智利", "name_en"=>"Chile"),
		array("code"=>"AI", "mobile_pre"=>"+1264", "name"=>"安圭拉", "name_en"=>"Anguilla"),
		array("code"=>"KY", "mobile_pre"=>"+1345", "name"=>"开曼群岛", "name_en"=>"Cayman Islands"),
		array("code"=>"GT", "mobile_pre"=>"+502", "name"=>"瓜地马拉", "name_en"=>"Guatemala"),
		array("code"=>"BR", "mobile_pre"=>"+55", "name"=>"巴西", "name_en"=>"Brazil"),
		array("code"=>"MZ", "mobile_pre"=>"+258", "name"=>"莫桑比克", "name_en"=>"Mozambique"),
		array("code"=>"SR", "mobile_pre"=>"+597", "name"=>"苏里南", "name_en"=>"Suriname"),
		array("code"=>"ML", "mobile_pre"=>"+223", "name"=>"马里", "name_en"=>"Mali"),
		array("code"=>"YE", "mobile_pre"=>"+967", "name"=>"也门", "name_en"=>"Yemen"),
		array("code"=>"ZA", "mobile_pre"=>"+27", "name"=>"南非", "name_en"=>"South Africa"),
		array("code"=>"ID", "mobile_pre"=>"+62", "name"=>"印度尼西亚", "name_en"=>"Indonesia"),
		array("code"=>"PH", "mobile_pre"=>"+63", "name"=>"菲律宾", "name_en"=>"Philippines"),
		array("code"=>"BH", "mobile_pre"=>"+973", "name"=>"巴林", "name_en"=>"Bahrain"),
		array("code"=>"VC", "mobile_pre"=>"+1784", "name"=>"圣文森特和格林纳丁斯", "name_en"=>"Saint Vincent and The Grenadines"),
		array("code"=>"HK", "mobile_pre"=>"+852", "name"=>"香港", "name_en"=>"Hong Kong"),
		array("code"=>"GY", "mobile_pre"=>"+592", "name"=>"圭亚那", "name_en"=>"Guyana"),
		array("code"=>"KG", "mobile_pre"=>"+996", "name"=>"吉尔吉斯斯坦", "name_en"=>"Kyrgyzstan"),
		array("code"=>"DJ", "mobile_pre"=>"+253", "name"=>"吉布提", "name_en"=>"Djibouti"),
		array("code"=>"PL", "mobile_pre"=>"+48", "name"=>"波兰", "name_en"=>"Poland"),
		array("code"=>"RU", "mobile_pre"=>"+7", "name"=>"俄罗斯", "name_en"=>"Russia"),
		array("code"=>"TC", "mobile_pre"=>"+1649", "name"=>"特克斯和凯科斯群岛", "name_en"=>"Turks and Caicos Islands"),
		array("code"=>"AG", "mobile_pre"=>"+1268", "name"=>"安提瓜和巴布达", "name_en"=>"Antigua and Barbuda"),
		array("code"=>"FM", "mobile_pre"=>"+691", "name"=>"密克罗尼西亚", "name_en"=>"Micronesia"),
		array("code"=>"PW", "mobile_pre"=>"+680", "name"=>"帕劳", "name_en"=>"Palau"),
		array("code"=>"CO", "mobile_pre"=>"+57", "name"=>"哥伦比亚", "name_en"=>"Colombia"),
		array("code"=>"CD", "mobile_pre"=>"+243", "name"=>"刚果民主共和国", "name_en"=>"Democratic Republic of the Congo"),
		array("code"=>"KP", "mobile_pre"=>"+850", "name"=>"朝鲜", "name_en"=>"North Korea"),
		array("code"=>"SA", "mobile_pre"=>"+966", "name"=>"沙特阿拉伯", "name_en"=>"Saudi Arabia"),
		array("code"=>"RO", "mobile_pre"=>"+40", "name"=>"罗马尼亚", "name_en"=>"Romania"),
		array("code"=>"BA", "mobile_pre"=>"+387", "name"=>"波斯尼亚和黑塞哥维那", "name_en"=>"Bosnia and Herzegovina"),
		array("code"=>"CG", "mobile_pre"=>"+242", "name"=>"刚果共和国", "name_en"=>"Republic Of The Congo"),
		array("code"=>"MK", "mobile_pre"=>"+389", "name"=>"马其顿", "name_en"=>"Macedonia"),
		array("code"=>"TL", "mobile_pre"=>"+670", "name"=>"东帝汶", "name_en"=>"East Timor"),
		array("code"=>"AR", "mobile_pre"=>"+54", "name"=>"阿根廷", "name_en"=>"Argentina"),
		array("code"=>"TG", "mobile_pre"=>"+228", "name"=>"多哥", "name_en"=>"Togo"),
		array("code"=>"QA", "mobile_pre"=>"+974", "name"=>"卡塔尔", "name_en"=>"Qatar"),
		array("code"=>"PY", "mobile_pre"=>"+595", "name"=>"巴拉圭", "name_en"=>"Paraguay"),
		array("code"=>"GU", "mobile_pre"=>"+1671", "name"=>"关岛", "name_en"=>"Guam"),
		array("code"=>"AO", "mobile_pre"=>"+244", "name"=>"安哥拉", "name_en"=>"Angola"),
		array("code"=>"AN", "mobile_pre"=>"+599", "name"=>"荷属安的列斯群岛", "name_en"=>"Netherlands Antilles"),
		array("code"=>"VN", "mobile_pre"=>"+84", "name"=>"越南", "name_en"=>"Vietnam"),
		array("code"=>"IR", "mobile_pre"=>"+98", "name"=>"伊朗", "name_en"=>"Iran"),
		array("code"=>"AM", "mobile_pre"=>"+374", "name"=>"亚美尼亚", "name_en"=>"Armenia"),
		array("code"=>"GP", "mobile_pre"=>"+590", "name"=>"瓜德罗普岛", "name_en"=>"Guadeloupe"),
		array("code"=>"KW", "mobile_pre"=>"+965", "name"=>"科威特", "name_en"=>"Kuwait"),
		array("code"=>"CR", "mobile_pre"=>"+506", "name"=>"哥斯达黎加", "name_en"=>"Costa Rica"),
		array("code"=>"TO", "mobile_pre"=>"+676", "name"=>"汤加", "name_en"=>"Tonga"),
		array("code"=>"LK", "mobile_pre"=>"+94", "name"=>"斯里兰卡", "name_en"=>"Sri Lanka"),
		array("code"=>"KR", "mobile_pre"=>"+82", "name"=>"韩国", "name_en"=>"South Korea"),
		array("code"=>"SY", "mobile_pre"=>"+963", "name"=>"叙利亚", "name_en"=>"Syria"),
		array("code"=>"AD", "mobile_pre"=>"+376", "name"=>"安道尔", "name_en"=>"Andorra"),
		array("code"=>"CH", "mobile_pre"=>"+41", "name"=>"瑞士", "name_en"=>"Switzerland"),
		array("code"=>"MS", "mobile_pre"=>"+1664", "name"=>"蒙特塞拉特岛", "name_en"=>"Montserrat"),
		array("code"=>"KE", "mobile_pre"=>"+254", "name"=>"肯尼亚", "name_en"=>"Kenya"),
		array("code"=>"SZ", "mobile_pre"=>"+268", "name"=>"斯威士兰", "name_en"=>"Swaziland"),
		array("code"=>"BT", "mobile_pre"=>"+975", "name"=>"不丹", "name_en"=>"Bhutan"),
		array("code"=>"EE", "mobile_pre"=>"+372", "name"=>"爱沙尼亚", "name_en"=>"Estonia"),
		array("code"=>"CV", "mobile_pre"=>"+238", "name"=>"开普", "name_en"=>"Cape Verde"),
		array("code"=>"NO", "mobile_pre"=>"+47", "name"=>"挪威", "name_en"=>"Norway"),
		array("code"=>"GN", "mobile_pre"=>"+224", "name"=>"几内亚", "name_en"=>"Guinea"),
		array("code"=>"MG", "mobile_pre"=>"+261", "name"=>"马达加斯加", "name_en"=>"Madagascar"),
		array("code"=>"LY", "mobile_pre"=>"+218", "name"=>"利比亚", "name_en"=>"Libya"),
		array("code"=>"FO", "mobile_pre"=>"+298", "name"=>"法罗群岛", "name_en"=>"Faroe Islands"),
		array("code"=>"NG", "mobile_pre"=>"+234", "name"=>"尼日利亚", "name_en"=>"Nigeria"),
		array("code"=>"TR", "mobile_pre"=>"+90", "name"=>"土耳其", "name_en"=>"Turkey"),
		array("code"=>"TW", "mobile_pre"=>"+886", "name"=>"台湾", "name_en"=>"Taiwan"),
		array("code"=>"NP", "mobile_pre"=>"+977", "name"=>"尼泊尔", "name_en"=>"Nepal"),
		array("code"=>"CU", "mobile_pre"=>"+53", "name"=>"古巴", "name_en"=>"Cuba"),
		array("code"=>"PK", "mobile_pre"=>"+92", "name"=>"巴基斯坦", "name_en"=>"Pakistan"),
		array("code"=>"RW", "mobile_pre"=>"+250", "name"=>"卢旺达", "name_en"=>"Rwanda"),
		array("code"=>"GE", "mobile_pre"=>"+995", "name"=>"格鲁吉亚", "name_en"=>"Georgia"),
		array("code"=>"BF", "mobile_pre"=>"+226", "name"=>"布基纳法索", "name_en"=>"Burkina Faso"),
		array("code"=>"IE", "mobile_pre"=>"+353", "name"=>"爱尔兰", "name_en"=>"Ireland"),
		array("code"=>"IN", "mobile_pre"=>"+91", "name"=>"印度", "name_en"=>"India"),
		array("code"=>"LA", "mobile_pre"=>"+856", "name"=>"老挝", "name_en"=>"Laos"),
		array("code"=>"VE", "mobile_pre"=>"+58", "name"=>"委内瑞拉", "name_en"=>"Venezuela"),
		array("code"=>"MC", "mobile_pre"=>"+377", "name"=>"摩纳哥", "name_en"=>"Monaco"),
		array("code"=>"LU", "mobile_pre"=>"+352", "name"=>"卢森堡", "name_en"=>"Luxembourg"),
		array("code"=>"JM", "mobile_pre"=>"+1876", "name"=>"牙买加", "name_en"=>"Jamaica"),
		array("code"=>"CY", "mobile_pre"=>"+357", "name"=>"塞浦路斯", "name_en"=>"Cyprus"),
		array("code"=>"DZ", "mobile_pre"=>"+213", "name"=>"阿尔及利亚", "name_en"=>"Algeria"),
		array("code"=>"AF", "mobile_pre"=>"+93", "name"=>"阿富汗", "name_en"=>"Afghanistan"),
		array("code"=>"IQ", "mobile_pre"=>"+964", "name"=>"伊拉克", "name_en"=>"Iraq"),
		array("code"=>"TJ", "mobile_pre"=>"+992", "name"=>"塔吉克斯坦", "name_en"=>"Tajikistan"),
		array("code"=>"LC", "mobile_pre"=>"+1758", "name"=>"圣露西亚", "name_en"=>"Saint Lucia"),
		array("code"=>"PA", "mobile_pre"=>"+507", "name"=>"巴拿马", "name_en"=>"Panama"),
		array("code"=>"VG", "mobile_pre"=>"+1340", "name"=>"英属处女群岛", "name_en"=>"Virgin Islands, British"),
		array("code"=>"LI", "mobile_pre"=>"+423", "name"=>"列支敦士登", "name_en"=>"Liechtenstein"),
		array("code"=>"SL", "mobile_pre"=>"+232", "name"=>"塞拉利昂", "name_en"=>"Sierra Leone"),
		array("code"=>"BE", "mobile_pre"=>"+32", "name"=>"比利时", "name_en"=>"Belgium"),
		array("code"=>"PS", "mobile_pre"=>"+970", "name"=>"巴勒斯坦领土", "name_en"=>"Palestinian Territory"),
		array("code"=>"GB", "mobile_pre"=>"+44", "name"=>"英国", "name_en"=>"United Kingdom"),
		array("code"=>"HN", "mobile_pre"=>"+504", "name"=>"洪都拉斯", "name_en"=>"Honduras"),
		array("code"=>"LS", "mobile_pre"=>"+266", "name"=>"莱索托", "name_en"=>"Lesotho"),
		array("code"=>"SN", "mobile_pre"=>"+221", "name"=>"塞内加尔", "name_en"=>"Senegal"),
		array("code"=>"NC", "mobile_pre"=>"+687", "name"=>"新喀里多尼亚", "name_en"=>"New Caledonia"),
		array("code"=>"MR", "mobile_pre"=>"+222", "name"=>"毛里塔尼亚", "name_en"=>"Mauritania"),
		array("code"=>"ET", "mobile_pre"=>"+251", "name"=>"埃塞俄比亚", "name_en"=>"Ethiopia"),
		array("code"=>"KH", "mobile_pre"=>"+855", "name"=>"柬埔寨", "name_en"=>"Cambodia"),
		array("code"=>"AE", "mobile_pre"=>"+971", "name"=>"阿拉伯联合酋长国", "name_en"=>"United Arab Emirates"),
		array("code"=>"GW", "mobile_pre"=>"+245", "name"=>"几内亚比绍共和国", "name_en"=>"Guinea-Bissau"),
		array("code"=>"ME", "mobile_pre"=>"+382", "name"=>"黑山", "name_en"=>"Montenegro"),
		array("code"=>"MV", "mobile_pre"=>"+960", "name"=>"马尔代夫", "name_en"=>"Maldives"),
		array("code"=>"LR", "mobile_pre"=>"+231", "name"=>"利比里亚", "name_en"=>"Liberia"),
		array("code"=>"GM", "mobile_pre"=>"+220", "name"=>"冈比亚", "name_en"=>"Gambia"),
		array("code"=>"ES", "mobile_pre"=>"+34", "name"=>"西班牙", "name_en"=>"Spain"),
		array("code"=>"PM", "mobile_pre"=>"+508", "name"=>"圣彼埃尔和密克隆岛", "name_en"=>"Saint Pierre and Miquelon"),
		array("code"=>"MY", "mobile_pre"=>"+60", "name"=>"马来西亚", "name_en"=>"Malaysia"),
		array("code"=>"TH", "mobile_pre"=>"+66", "name"=>"泰国", "name_en"=>"Thailand"),
		array("code"=>"GQ", "mobile_pre"=>"+240", "name"=>"赤道几内亚", "name_en"=>"Equatorial Guinea"),
		array("code"=>"PF", "mobile_pre"=>"+689", "name"=>"法属波利尼西亚", "name_en"=>"French Polynesia"),
		array("code"=>"BS", "mobile_pre"=>"+1242", "name"=>"巴哈马", "name_en"=>"Bahamas"),
		array("code"=>"KI", "mobile_pre"=>"+686", "name"=>"基里巴斯", "name_en"=>"Kiribati"),
		array("code"=>"SG", "mobile_pre"=>"+65", "name"=>"新加坡", "name_en"=>"Singapore"),
		array("code"=>"AU", "mobile_pre"=>"+61", "name"=>"澳大利亚", "name_en"=>"Australia"),
		array("code"=>"MU", "mobile_pre"=>"+230", "name"=>"毛里求斯", "name_en"=>"Mauritius"),
		array("code"=>"SE", "mobile_pre"=>"+46", "name"=>"瑞典", "name_en"=>"Sweden"),
		array("code"=>"DE", "mobile_pre"=>"+49", "name"=>"德国", "name_en"=>"Germany"),
		array("code"=>"GF", "mobile_pre"=>"+594", "name"=>"法属圭亚那", "name_en"=>"French Guiana"),
		array("code"=>"UG", "mobile_pre"=>"+256", "name"=>"乌干达", "name_en"=>"Uganda"),
		array("code"=>"BI", "mobile_pre"=>"+257", "name"=>"布隆迪", "name_en"=>"Burundi"),
		array("code"=>"GH", "mobile_pre"=>"+233", "name"=>"加纳", "name_en"=>"Ghana"),
		array("code"=>"BW", "mobile_pre"=>"+267", "name"=>"博茨瓦纳", "name_en"=>"Botswana"),
		array("code"=>"SK", "mobile_pre"=>"+421", "name"=>"斯洛伐克", "name_en"=>"Slovakia"),
		array("code"=>"VI", "mobile_pre"=>"+1284", "name"=>"美属维尔京群岛", "name_en"=>"Virgin Islands, US"),
		array("code"=>"GR", "mobile_pre"=>"+30", "name"=>"希腊", "name_en"=>"Greece"),
		array("code"=>"FJ", "mobile_pre"=>"+679", "name"=>"斐济", "name_en"=>"Fiji"),
		array("code"=>"IL", "mobile_pre"=>"+972", "name"=>"以色列", "name_en"=>"Israel"),
		array("code"=>"MQ", "mobile_pre"=>"+596", "name"=>"马提尼克", "name_en"=>"Martinique"),
		array("code"=>"UY", "mobile_pre"=>"+598", "name"=>"乌拉圭", "name_en"=>"Uruguay"),
		array("code"=>"MD", "mobile_pre"=>"+373", "name"=>"摩尔多瓦", "name_en"=>"Moldova"),
		array("code"=>"CF", "mobile_pre"=>"+236", "name"=>"中非共和国", "name_en"=>"Central African Republic"),
		array("code"=>"BJ", "mobile_pre"=>"+229", "name"=>"贝宁", "name_en"=>"Benin"),
		array("code"=>"BG", "mobile_pre"=>"+359", "name"=>"保加利亚", "name_en"=>"Bulgaria"),
		array("code"=>"EG", "mobile_pre"=>"+20", "name"=>"埃及", "name_en"=>"Egypt"),
		array("code"=>"AZ", "mobile_pre"=>"+994", "name"=>"阿塞拜疆", "name_en"=>"Azerbaijan"),
		array("code"=>"KN", "mobile_pre"=>"+1869", "name"=>"圣基茨和尼维斯", "name_en"=>"Saint Kitts and Nevis"),
		array("code"=>"MM", "mobile_pre"=>"+95", "name"=>"缅甸", "name_en"=>"Myanmar"),
		array("code"=>"WS", "mobile_pre"=>"+685", "name"=>"萨摩亚", "name_en"=>"Samoa"),
		array("code"=>"SO", "mobile_pre"=>"+252", "name"=>"索马里", "name_en"=>"Somalia"),
		array("code"=>"GI", "mobile_pre"=>"+350", "name"=>"直布罗陀", "name_en"=>"Gibraltar"),
		array("code"=>"MX", "mobile_pre"=>"+52", "name"=>"墨西哥", "name_en"=>"Mexico"),
		array("code"=>"SV", "mobile_pre"=>"+503", "name"=>"萨尔瓦多", "name_en"=>"El Salvador"),
		array("code"=>"ZW", "mobile_pre"=>"+263", "name"=>"津巴布韦", "name_en"=>"Zimbabwe"),
		array("code"=>"MW", "mobile_pre"=>"+265", "name"=>"马拉维", "name_en"=>"Malawi"),
		array("code"=>"CZ", "mobile_pre"=>"+420", "name"=>"捷克", "name_en"=>"Czech Republic"),
		array("code"=>"BO", "mobile_pre"=>"+591", "name"=>"玻利维亚", "name_en"=>"Bolivia"),
		array("code"=>"SI", "mobile_pre"=>"+386", "name"=>"斯洛文尼亚", "name_en"=>"Slovenia"),
		array("code"=>"NZ", "mobile_pre"=>"+64", "name"=>"新西兰", "name_en"=>"New Zealand"),
		array("code"=>"MO", "mobile_pre"=>"+853", "name"=>"澳门", "name_en"=>"Macau"),
		array("code"=>"SC", "mobile_pre"=>"+248", "name"=>"塞舌尔", "name_en"=>"Seychelles"),
		array("code"=>"AT", "mobile_pre"=>"+43", "name"=>"奥地利", "name_en"=>"Austria"),
		array("code"=>"MN", "mobile_pre"=>"+976", "name"=>"蒙古", "name_en"=>"Mongolia"),
		array("code"=>"LT", "mobile_pre"=>"+370", "name"=>"立陶宛", "name_en"=>"Lithuania"),
		array("code"=>"US", "mobile_pre"=>"+1", "name"=>"美国", "name_en"=>"United States"),
		array("code"=>"CK", "mobile_pre"=>"+682", "name"=>"库克群岛", "name_en"=>"Cook Islands"),
		array("code"=>"GL", "mobile_pre"=>"+299", "name"=>"格陵兰岛", "name_en"=>"Greenland"),
		array("code"=>"SS", "mobile_pre"=>"+211", "name"=>"南苏丹", "name_en"=>"South Sudan"),
		array("code"=>"PG", "mobile_pre"=>"+675", "name"=>"巴布亚新几内亚", "name_en"=>"Papua New Guinea"),
		array("code"=>"DK", "mobile_pre"=>"+45", "name"=>"丹麦", "name_en"=>"Denmark"),
		array("code"=>"EC", "mobile_pre"=>"+593", "name"=>"厄瓜多尔", "name_en"=>"Ecuador"),
		array("code"=>"MA", "mobile_pre"=>"+212", "name"=>"摩洛哥", "name_en"=>"Morocco"),
		array("code"=>"VU", "mobile_pre"=>"+678", "name"=>"瓦努阿图", "name_en"=>"Vanuatu"),
		array("code"=>"SB", "mobile_pre"=>"+677", "name"=>"所罗门群岛", "name_en"=>"Solomon Islands"),
		array("code"=>"BN", "mobile_pre"=>"+673", "name"=>"文莱", "name_en"=>"Brunei"),
		array("code"=>"SD", "mobile_pre"=>"+249", "name"=>"苏丹", "name_en"=>"Sudan"),
		array("code"=>"AL", "mobile_pre"=>"+355", "name"=>"阿尔巴尼亚", "name_en"=>"Albania"),
		array("code"=>"BB", "mobile_pre"=>"+1246", "name"=>"巴巴多斯", "name_en"=>"Barbados"),
		array("code"=>"ST", "mobile_pre"=>"+239", "name"=>"圣多美和普林西比", "name_en"=>"Sao Tome and Principe"),
		array("code"=>"FI", "mobile_pre"=>"+358", "name"=>"芬兰", "name_en"=>"Finland"),
		array("code"=>"GD", "mobile_pre"=>"+1473", "name"=>"格林纳达", "name_en"=>"Grenada"),
		array("code"=>"JO", "mobile_pre"=>"+962", "name"=>"约旦", "name_en"=>"Jordan"),
		array("code"=>"MT", "mobile_pre"=>"+356", "name"=>"马耳他", "name_en"=>"Malta"),
		array("code"=>"FR", "mobile_pre"=>"+33", "name"=>"法国", "name_en"=>"France"),
		array("code"=>"NA", "mobile_pre"=>"+264", "name"=>"纳米比亚", "name_en"=>"Namibia"),
		array("code"=>"RS", "mobile_pre"=>"+381", "name"=>"塞尔维亚", "name_en"=>"Serbia"),
		array("code"=>"TD", "mobile_pre"=>"+235", "name"=>"乍得", "name_en"=>"Chad"),
		array("code"=>"CA", "mobile_pre"=>"+1", "name"=>"加拿大", "name_en"=>"Canada"),
		array("code"=>"CI", "mobile_pre"=>"+225", "name"=>"象牙海岸", "name_en"=>"Ivory Coast"),
		array("code"=>"RE", "mobile_pre"=>"+262", "name"=>"留尼汪", "name_en"=>"Réunion Island"),
		array("code"=>"GA", "mobile_pre"=>"+241", "name"=>"加蓬", "name_en"=>"Gabon"),
		array("code"=>"DM", "mobile_pre"=>"+1767", "name"=>"多米尼加", "name_en"=>"Dominica"),
		array("code"=>"JP", "mobile_pre"=>"+81", "name"=>"日本", "name_en"=>"Japan"),
		array("code"=>"TT", "mobile_pre"=>"+1868", "name"=>"特立尼达和多巴哥", "name_en"=>"Trinidad and Tobago"),
		array("code"=>"UA", "mobile_pre"=>"+380", "name"=>"乌克兰", "name_en"=>"Ukraine"),
		array("code"=>"TZ", "mobile_pre"=>"+255", "name"=>"坦桑尼亚", "name_en"=>"Tanzania"),
		array("code"=>"TM", "mobile_pre"=>"+993", "name"=>"土库曼斯坦", "name_en"=>"Turkmenistan"),
		array("code"=>"LB", "mobile_pre"=>"+961", "name"=>"黎巴嫩", "name_en"=>"Lebanon"),
		array("code"=>"OM", "mobile_pre"=>"+968", "name"=>"阿曼", "name_en"=>"Oman"),
		array("code"=>"IT", "mobile_pre"=>"+39", "name"=>"意大利", "name_en"=>"Italy"),
		array("code"=>"HR", "mobile_pre"=>"+385", "name"=>"克罗地亚", "name_en"=>"Croatia"),
		array("code"=>"PR", "mobile_pre"=>"+1787", "name"=>"波多黎各", "name_en"=>"Puerto Rico"),
		array("code"=>"HT", "mobile_pre"=>"+509", "name"=>"海地", "name_en"=>"Haiti"),
		array("code"=>"ZM", "mobile_pre"=>"+260", "name"=>"赞比亚", "name_en"=>"Zambia"),
		array("code"=>"PE", "mobile_pre"=>"+51", "name"=>"秘鲁", "name_en"=>"Peru"),
		array("code"=>"HU", "mobile_pre"=>"+36", "name"=>"匈牙利", "name_en"=>"Hungary"),
		array("code"=>"IS", "mobile_pre"=>"+354", "name"=>"冰岛", "name_en"=>"Iceland"),
		array("code"=>"CM", "mobile_pre"=>"+237", "name"=>"喀麦隆", "name_en"=>"Cameroon"),
		array("code"=>"KZ", "mobile_pre"=>"+7", "name"=>"哈萨克斯坦", "name_en"=>"Kazakhstan"),
		array("code"=>"NI", "mobile_pre"=>"+505", "name"=>"尼加拉瓜", "name_en"=>"Nicaragua"),
		array("code"=>"NL", "mobile_pre"=>"+31", "name"=>"荷兰", "name_en"=>"Netherlands"),
		array("code"=>"BD", "mobile_pre"=>"+880", "name"=>"孟加拉国", "name_en"=>"Bangladesh"),
		array("code"=>"BZ", "mobile_pre"=>"+501", "name"=>"伯利兹", "name_en"=>"Belize"),
		array("code"=>"BM", "mobile_pre"=>"+1441", "name"=>"百慕大群岛", "name_en"=>"Bermuda"),
		array("code"=>"AW", "mobile_pre"=>"+297", "name"=>"阿鲁巴", "name_en"=>"Aruba"),
		array("code"=>"LV", "mobile_pre"=>"+371", "name"=>"拉脱维亚", "name_en"=>"Latvia"),
		array("code"=>"SM", "mobile_pre"=>"+378", "name"=>"圣马力诺", "name_en"=>"San Marino"),
	);
	public static function getMobile($mobile){
		return str_replace("+86","",$mobile);
	}
	public static function mobile($mobile){
		if($mobile{0}=="+"){
			foreach(self::$mobile_country as $mc){
				$mobile2 = str_replace($mc['mobile_pre'], "", $mobile,$ct);
				if($ct>0){
					if($mc['code']=="CN"){
						if(preg_match('/^1[34578][0-9]{9}$/',$mobile2)){
							return true;
						}
						return false;
					}
					if(is_numeric($mobile2)){
						return true;
					}
				}
			}
			return false;
		}else{
			if(preg_match('/^1[34578][0-9]{9}$/',$mobile)){
				return true;
			}
			return false;
		}
	}
	public static function email($email, $checkdns=true){
		$isValid = true;
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex){
			$isValid = false;
		}else{
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64){
				// local part length exceeded
				$isValid = false;
			}else if ($domainLen < 1 || $domainLen > 255){
				// domain part length exceeded
				$isValid = false;
			}else if ($local[0] == '.' || $local[$localLen-1] == '.'){
				// local part starts or ends with '.'
				$isValid = false;
			}else if (preg_match('/\\.\\./', $local)){
				// local part has two consecutive dots
				$isValid = false;
			}else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)){
				// character not valid in domain part
				$isValid = false;
			}else if (preg_match('/\\.\\./', $domain)){
				// domain part has two consecutive dots
				$isValid = false;
			}else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))){
				// character not valid in local part unless 
				// local part is quoted
				if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))){
					$isValid = false;
				}
			}
			if ($checkdns && $isValid && !(checkdnsrr($domain,"MX"))){
				// domain not found in DNS
				$isValid = false;
			}
			return $isValid;
		}
	}
}
