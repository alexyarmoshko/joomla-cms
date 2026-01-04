<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_ystides
 *
 * @copyright   (C) 2025 YSTides
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.collapse');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('mod_ystides', 'mod_ystides/template.css');

$moduleClassSfx = isset($moduleclass_sfx) ? $moduleclass_sfx : '';
$stationHeader = $stationName ?? '';
$dbErrorMessage = $dbError ?? '';
$fetchErrorMessage = $fetchError ?? '';
$rowsData = $rows ?? [];
$moduleId = isset($module) ? (int) $module->id : rand(1000, 9999);
$mainId = 'ystides-main-' . $moduleId;
$infoId = 'ystides-info-' . $moduleId;
$progressMin = 20;
$progressMax = 120;
?>
<div class="mod-ystides<?php echo htmlspecialchars($moduleClassSfx, ENT_QUOTES, 'UTF-8'); ?>">
	<?php if ($dbErrorMessage !== ''): ?>
		<div class="alert alert-warning">
			<?php echo htmlspecialchars($dbErrorMessage, ENT_QUOTES, 'UTF-8'); ?>
		</div>
	<?php elseif ($fetchErrorMessage !== ''): ?>
		<div class="alert alert-warning">
			<?php echo htmlspecialchars($fetchErrorMessage, ENT_QUOTES, 'UTF-8'); ?>
		</div>
	<?php else: ?>
		<div class="mod-ystides__wrap collapse multi-collapse show" id="<?php echo $mainId; ?>">
			<div class="d-flex align-items-center justify-content-between mb-2">
				<div class="fw-semibold">
					<?php echo Text::sprintf('MOD_YSTIDES_HEADER_DESC', htmlspecialchars($stationHeader, ENT_QUOTES, 'UTF-8')); ?>
				</div>
				<button type="button" class="btn btn-outline-secondary btn-sm rounded-circle" data-bs-toggle="collapse"
					data-bs-target=".multi-collapse" aria-controls="<?php echo $infoId; ?>" aria-expanded="false"
					aria-label="<?php echo Text::_('MOD_YSTIDES_INFO'); ?>"><i class="fa fa-circle-info"></i></button>
			</div>
			<table class="table table-striped mod-ystides-table mb-0">
				<thead>
					<tr>
						<th scope="col" class="mod-ystides-table-subheader-col1" width="16">
							<i class="fa-solid fa-moon" title="<?php echo Text::_('MOD_YSTIDES_MOON_PHASES'); ?>"></i>
						</th>
						<th colspan="2" scope="col" class="mod-ystides-table-subheader-col2">
							<?php echo Text::_('MOD_YSTIDES_HEADING_TIME'); ?>
						</th>
						<th scope="col" class="mod-ystides-table-subheader-col3">
							<?php echo Text::_('MOD_YSTIDES_HEADING_WLM'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($rowsData)): ?>
						<tr class="mod-ystides-empty">
							<td colspan="4"><?php echo Text::_('MOD_YSTIDES_NO_DATA'); ?></td>
						</tr>
					<?php else: ?>
						<?php $prevMeanD = null; ?>
						<?php foreach ($rowsData as $row): ?>
							<?php
							$coefValue = $row['coef'];
							$coefPercent = null;
							$coefLabel = '';
							$coefColor = '';

							if ($coefValue !== null) {
								$coefPercent = max(0, min(100, $coefValue - 20));
								$coefLabel = Text::sprintf('MOD_YSTIDES_TIDE_COEFFICIENT_VALUE', (int) $coefValue);

								if ($coefValue < 50) {
									$coefColor = 'low';
								} elseif ($coefValue < 70) {
									$coefColor = 'average';
								} elseif ($coefValue < 90) {
									$coefColor = 'high';
								} else {
									$coefColor = 'very-high';
								}
							}
							?>
							<tr>
								<td class="mod-ystides-table-data-col1" width="16">
									<?php if ($prevMeanD !== $row['meand']): ?>
										<?php if (!empty($row['moonPhase'])): ?>
											<div class="ystides-moon ystides-moon-<?php echo $row['moonPhase']; ?>-icon"
												title="<?php echo Text::_('MOD_YSTIDES_MOON_' . strtoupper($row['moonPhase'])); ?>">&nbsp;
											</div>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<td class="mod-ystides-table-data-col2">
									<?php if ($prevMeanD !== $row['meand']): ?>
										<span
											title="<?php echo htmlspecialchars($row['titledt'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo str_replace(' ', '&nbsp;', HTMLHelper::_('date', $row['meandt'], 'j M', 'UTC')); ?></span>
									<?php endif; ?>
								</td>
								<td class="mod-ystides-table-data-col3"
									title="<?php echo htmlspecialchars($row['titledt'], ENT_QUOTES, 'UTF-8'); ?>">
									<?php echo str_replace(' ', '&nbsp;', HTMLHelper::_('date', $row['meandt'], 'H:i', 'UTC')); ?>&nbsp;<span
										style="font-size: smaller;">(&plusmn;<?php echo $row['deltadt']; ?>)</span>
								</td>
								<td class="mod-ystides-table-data-col4"
									title="<?php echo htmlspecialchars($row['hint'], ENT_QUOTES, 'UTF-8'); ?>">
									<div class="position-relative overflow-hidden">
										<?php if ($coefPercent !== null): ?>
											<div class="progress flex-row-reverse position-absolute start-0 top-0 w-100 h-100 opacity-25"
												style="pointer-events: none;">
												<div class="progress-bar ystides-coeff-<?php echo $coefColor; ?>" role="progressbar"
													style="width: <?php echo $coefPercent; ?>%"
													aria-valuenow="<?php echo (int) $coefValue; ?>" aria-valuemin="20"
													aria-valuemax="120">
												</div>
											</div>
										<?php endif; ?>
										<div
											class="d-flex align-items-center justify-content-between gap-2 position-relative ystides-<?php echo $row['symbol'] ?>-tide-icon">
											<span><?php echo $row['wlm']; ?></span>
											<?php if ($coefPercent !== null): ?>
												<span class="badge ystides-coeff-value-<?php echo $coefColor; ?>"
													title="<?php echo htmlspecialchars($coefLabel, ENT_QUOTES, 'UTF-8'); ?>"><?php echo (int) $coefValue; ?></span>
											<?php endif; ?>
										</div>
									</div>
								</td>
							</tr>
							<?php $prevMeanD = $row['meand']; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<div class="mod-ystides__wrap collapse multi-collapse mod-ystides-info" id="<?php echo $infoId; ?>"
			data-bs-parent=".mod-ystides">
			<div class="d-flex align-items-center justify-content-between mb-2">
				<button type="button" class="btn btn-outline-secondary btn-sm rounded-circle" data-bs-toggle="collapse"
					data-bs-target=".multi-collapse" aria-controls="<?php echo $mainId; ?>" aria-expanded="false"
					aria-label="<?php echo Text::_('MOD_YSTIDES_BACK'); ?>">&larr;</button>
				<div class="fw-semibold"><?php echo Text::_('MOD_YSTIDES_INFO'); ?></div>
			</div>
			<div class="card">
				<div class="card-body overflow-auto">
					<p><?php echo Text::_('MOD_YSTIDES_INFO_1'); ?></p>
					<p><?php echo Text::_('MOD_YSTIDES_INFO_2'); ?></p>
					<p><?php echo Text::_('MOD_YSTIDES_INFO_3'); ?></p>
					<p><?php echo Text::_('MOD_YSTIDES_INFO_4'); ?></p>
					<p><?php echo Text::_('MOD_YSTIDES_INFO_5'); ?></p>
					<p><?php echo Text::_('MOD_YSTIDES_INFO_6'); ?></p>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>