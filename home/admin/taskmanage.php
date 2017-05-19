<!--{template admin/tpl/header}-->

<div class="mainarea">
	<div class="maininner">
	
		<div class="tabs_header">
			<ul class="tabs">
				<li$actives[1]><a href="admincp.php?ac=$ac&perpage=20&status=1&searchsubmit=1"><span>待处理举报</span></a></li>
				<li$actives[0]><a href="admincp.php?ac=$ac&perpage=20&status=0&searchsubmit=1"><span>已禁止举报</span></a></li>
			</ul>
		</div>

		<form method="get" action="admincp.php">
			<div class="block style4">
				
				<table cellspacing="3" cellpadding="3">
				
				<tr>
					<th>举报类型</th><td>
					<select name="idtype">
					<option value="">不限</option>
					<option value="picid"<!--{if $_GET[idtype] == 'picid'}--> selected<!--{/if}-->>图片</option>
					<option value="albumid"<!--{if $_GET[idtype] == 'albumid'}--> selected<!--{/if}-->>相册</option>
					<option value="blogid"<!--{if $_GET[idtype] == 'blogid'}--> selected<!--{/if}-->>日志</option>
					<option value="tagid"<!--{if $_GET[idtype] == 'tagid'}--> selected<!--{/if}-->>群组</option>
					<option value="tid"<!--{if $_GET[idtype] == 'tid'}--> selected<!--{/if}-->>话题</option>
					<option value="uid"<!--{if $_GET[idtype] == 'uid'}--> selected<!--{/if}-->>空间</option>
					<option value="sid"<!--{if $_GET[idtype] == 'sid'}--> selected<!--{/if}-->>分享</option>
					<option value="pid"<!--{if $_GET[idtype] == 'pid'}--> selected<!--{/if}-->>投票</option>
					<option value="eventid"<!--{if $_GET[idtype] == 'eventid'}--> selected<!--{/if}-->>活动</option>
					<option value="comment"<!--{if $_GET[idtype] == 'comment'}--> selected<!--{/if}-->>评论</option>
					<option value="post"<!--{if $_GET[idtype] == 'post'}--> selected<!--{/if}-->>话题回复</option>
					</select>
					</td>
					<th>举报状态</th><td>
					<select name="status">
					<option value="2">不限</option>
					<option value="0"<!--{if $_GET[status] == '0'}--> selected<!--{/if}-->>已忽略</option>
					<option value="1"<!--{if $_GET[status] == '1'}--> selected<!--{/if}-->>待处理</option>
					</select></td>
				</tr>
				
				<tr><th>举报次数</th><td colspan="3">
					<input type="text" name="num1" value="$_GET[num1]" size="10"> ~
					<input type="text" name="num2" value="$_GET[num2]" size="10">
				</td></tr>
				
		
				<tr><th>结果排序</th>
				<td colspan="3">
				<select name="orderby">
				<option value="">默认排序</option>
				<option value="dateline"$orderby[dateline]>举报时间</option>
				<option value="num"$orderby[viewnum]>举报数</option>
				</select>
				<select name="ordersc">
				<option value="desc"$ordersc[desc]>递减</option>
				<option value="asc"$ordersc[asc]>递增</option>
				</select>
				<select name="perpage">
				<option value="20"$perpages[20]>每页显示20个</option>
				<option value="50"$perpages[50]>每页显示50个</option>
				<option value="100"$perpages[100]>每页显示100个</option>
				<option value="1000"$perpages[1000]>一次处理1000个</option>
				</select>
				<input type="hidden" name="ac" value="report">
				<input type="submit" name="searchsubmit" value="搜索" class="submit">
				</td>
				</tr>
				</table>
		
			</div>
		</form>
	
	<!--{if $list}-->
	
		<form method="post" action="admincp.php?ac=report">
		<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
		<div class="bdrcontent">

		<!--{if $perpage>100}-->
			<p>总共有满足条件的数据 <strong>$count</strong> 个</p>
			<!--{loop $list $value}-->
			<input type="hidden" name="ids[]" value="$value[rid]">
			<!--{/loop}-->
		
		<!--{else}-->
			<table cellspacing="0" cellpadding="0" class="formtable" border="0">
			<tr>
				<td width="25">&nbsp;</td>
				<th>内容</th>
				<th width="50"><a href="admincp.php?ac=$ac&perpage=20&orderby=num&ordersc=$scstr&status=$_GET[status]&searchsubmit=1">次数</a></th>
				<th width="120"><a href="admincp.php?ac=$ac&perpage=20&orderby=dateline&ordersc=$scstr&status=$_GET[status]&searchsubmit=1">时间</a></th>
				<th width="140">操作</th>
			</tr>
			
			<!--{loop $list $key $report}-->
				<!--{loop $report $value}-->
				<tr <!--{if $value[new]}-->bgcolor="#F2F9FD"<!--{/if}-->>
					<td>
						<input type="checkbox" name="ids[]" value="$value[rid]">
					</td>
					<td>
						<!--{if empty($value[info])}-->
							信息已被删除<br>
						<!--{else}-->
						<!--{if $key=='blog'}-->
						<a href="space.php?uid=$value[info][uid]&do=blog&id=$value[info][blogid]" target="_blank">$value[info][subject]</a><br>
						<!--{elseif $key=='pic'}-->
						<a href="space.php?uid=$value[info][uid]&do=album&picid=$value[info][picid]" target="_blank"><img src="$value[info][pic]" width="90" alt="$value[info][filename]"></a><br>
						$value[info][title]<br>
						<!--{elseif $key=='album'}-->
						<a href="space.php?uid=$value[info][uid]&do=album&id=$value[info][albumid]" target="_blank"><img src="$value[info][pic]" alt="$value[info][albumname]" width="100" height="90"></a><br>
						$value[info][albumname]<br>
						<!--{elseif $key=='thread'}-->
						<a href="space.php?do=thread&id=$value[info][tid]" target="_blank">$value[info][subject]</a><br>
						<!--{elseif $key=='mtag'}-->
						<a href="space.php?do=mtag&tagid=$value[info][tagid]" target="_blank">$value[info][tagname]</a><br>
						<!--{elseif $key=='share'}-->
						<p><a href="admincp.php?ac=share&uid=$value[info][uid]">$value[info][username]</a> <a href="space.php?uid=$value[info][uid]&do=share&id=$value[info][sid]">$value[info][title_template]</a> &nbsp;<!--{date('Y-m-d H:i', $value[info][dateline])}--></p>
							$value[info][body_template]<br>
						<!--{elseif $key=='space'}-->
						<a href="space.php?uid=$value[info][uid]" target="_blank"><!--{avatar($value[info][uid],middle)}--></a><br>
						用户名: <a href="space.php?uid=$value[info][uid]">$value[info][username]</a><br>
						<!--{elseif $key=='poll'}-->
						<a href="space.php?uid=$value[info][uid]&do=poll&pid=$value[info][pid]" target="_blank">$value[info][subject]</a><br>
						<!--{elseif $key=='event'}-->
						<a href="space.php?uid=$value[info][uid]&do=event&id=$value[info][eventid]" target="_blank">$value[info][title]</a><br>
						<!--{elseif $key=='comment'}-->
						<a href="$value[info][url]" target="_blank">$value[info][message](查看详情)</a><br>
						<!--{elseif $key=='post'}-->
						<a href="space.php?do=thread&id=$value[info][tid]&pid=$value[info][pid]" target="_blank">$value[info][message](查看详情)</a><br>
						<!--{/if}-->
						<!--{/if}-->
						<strong>举报理由:</strong><br>
						<ul>$value[reason]</ul>
					</td>
					<td>$value[num]</td>
					<td>
						类型:
						<!--{if $key=='blog'}-->
						日志
						<!--{elseif $key=='pic'}-->
						图片
						<!--{elseif $key=='album'}-->
						相册
						<!--{elseif $key=='thread'}-->
						话题
						<!--{elseif $key=='mtag'}-->
						群组
						<!--{elseif $key=='share'}-->
						分享
						<!--{elseif $key=='space'}-->
						空间
						<!--{elseif $key=='poll'}-->
						投票
						<!--{elseif $key=='event'}-->
						活动
						<!--{elseif $key=='comment'}-->
						评论
						<!--{elseif $key=='post'}-->
						话题回复
						<!--{/if}-->
						<br>
						查阅状态:<!--{if $value[new]}-->未读<!--{else}-->已读<!--{/if}--><br>
						<!--{date('Y-m-d',$value[dateline])}-->
					</td>
					<td>
						<a href="admincp.php?ac=report&rid=$value[rid]&op=ignore">禁止举报</a><br>
						<a href="admincp.php?ac=report&rid=$value[rid]&op=delete" onclick="return confirm('本操作不可恢复，确认继续？');">删除举报</a><br>
						<a href="admincp.php?ac=report&rid=$value[rid]&op=delete&subop=delinfo" onclick="return confirm('本操作不可恢复，确认继续？');">删除举报及<!--{if $key=='blog'}-->
							日志信息
							<!--{elseif $key=='pic'}-->
							图片信息
							<!--{elseif $key=='album'}-->
							相册信息
							<!--{elseif $key=='thread'}-->
							话题信息
							<!--{elseif $key=='mtag'}-->
							群组
							<!--{elseif $key=='share'}-->
							分享信息
							<!--{elseif $key=='space'}-->
							空间
							<!--{elseif $key=='poll'}-->
							投票信息
							<!--{elseif $key=='event'}-->
							活动信息
							<!--{elseif $key=='comment'}-->
							评论信息
							<!--{elseif $key=='post'}-->
							话题回复信息
							<!--{/if}-->
						</a>
					</td>
				</tr>
				<!--{/loop}-->
			<!--{/loop}-->
			</table>
		<!--{/if}-->
		</div>
		
		<div class="footactions">
			<!--{if $perpage<=100}--><input type="checkbox" id="chkall" name="chkall" onclick="checkAll(this.form, 'ids')">全选<!--{/if}-->
			<input type="hidden" name="mpurl" value="$mpurl">
			操作类型：
			<input type="radio" name="optype" value="1" checked>禁止举报
			<input type="radio" name="optype" value="2">删除举报
			<input type="radio" name="optype" value="3">删除举报及信息或者空间
			<input type="submit" name="listsubmit" value="批量操作" class="submit" onclick="return confirm('本操作不可恢复，确认继续？');">
		
			<div class="pages">$multi</div>
		</div>
		</form>
	<!--{else}-->
		<div class="bdrcontent">
			<div class="title" id="base">
				<h3>
					<a href="admincp.php?ac=$ac&perpage=20&status=1&searchsubmit=1">待处理举报</a> | 
					<a href="admincp.php?ac=$ac&perpage=20&status=0&searchsubmit=1">已忽略举报</a>
				</h3>
			</div>
			<p>指定条件下还没有数据</p>
		</div>
	<!--{/if}-->
	</div>
</div>


<div class="side">
	<!--{template admin/tpl/side}-->
</div>

<!--{template admin/tpl/footer}-->