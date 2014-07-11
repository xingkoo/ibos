<div id="verify_dialog" style="width: 500px;">
	<table class="table table-head-condensed table-head-inverse">
		<thead>
			<tr>
				<th width="120"><?php echo $lang["Check type"];?></th>
				<th><?php echo $lang["Check result"];?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="vat xwb"><?php echo $lang["Agent check"];?></td>
				<td>
					<?php if (empty($result["user"])) : ?>
						<span class="pull-left">
							<i class="o-tip-success"></i>
						</span>
						<div class="media-body">
							<div class="xcm mbs"><?php echo $lang["Normal"];?></div>
						</div>
					<?php else : ?>
						<div class="media">
							<span class="pull-left">
								<i class="o-tip-danger"></i>
							</span>
							<div class="media-body">
								<div class="xcm mbs"><?php echo $lang["The following steps to host is not specified"];?></div>
								<div>
									<?php foreach ($result["user"] as $step => $name ) : ?>
										<span class="fss ilsep"><em class="label"><?php echo $step;?></em> <?php echo $name;?></span>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td class="vat xwb"><?php echo $lang["Steps check"];?></td>
				<td>
					<?php if ($result["checkCicrulating"]) : ?>
						<span class="pull-left">
							<i class="o-tip-success"></i>
						</span>
						<div class="media-body">
							<div class="xcm mbs"><?php echo $lang["Normal"];?></div>
						</div>
					<?php else : ?>
						<div class="media">
							<span class="pull-left">
								<i class="o-tip-danger"></i>
							</span>
							<div class="media-body">
								<div class="xcm mbs"><?php echo $lang["The following steps without the next step"];?></div>
								<div>
									<?php foreach ($result["circulating"] as $step => $info ) : ?>
										<?php if ((0 < $step) && !empty($info["error"])) : ?>
											<span class="fss ilsep"><em class="label"><?php echo $step;?></em> <?php echo $info["name"];?></span>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td class="vat xwb"><?php echo $lang["Writeable check"];?></td>
				<td>
					<?php if (empty($result["writable"])) : ?>
						<span class="pull-left">
							<i class="o-tip-success"></i>
						</span>
						<div class="media-body">
							<div class="xcm mbs"><?php echo $lang["Normal"];?></div>
						</div>
					<?php else : ?>
						<div class="media">
							<span class="pull-left">
								<i class="o-tip-danger"></i>
							</span>
							<div class="media-body">
								<div class="xcm mbs"><?php echo $lang["These steps do not specify a writable field"];?></div>
								<?php foreach ($result["writable"] as $step => $name ) : ?>
									<span class="fss ilsep"><em class="label"><?php echo $step;?></em> <?php echo $name;?></span>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>