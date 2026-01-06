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
$headerWarningIcon = $headerWarning ?? null;
$moduleId = isset($module) ? (int) $module->id : rand(1000, 9999);
$mainId = 'ystides-main-' . $moduleId;
$infoId = 'ystides-info-' . $moduleId;
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
					<?php if ($headerWarningIcon): ?>
						<a href="https://www.met.ie/warnings-today.html" target="_blank" class="ystides-header-warning">
							<img src="/media/mod_ystides/images/warning-<?php echo htmlspecialchars($headerWarningIcon, ENT_QUOTES, 'UTF-8'); ?>@2x.png"
								width="16" height="16" style="margin-top:-2px"
								title="<?php echo Text::_('MOD_YSTIDES_WARNING_' . strtoupper(str_replace('-', '_', $headerWarningIcon))); ?>"
								alt="<?php echo Text::_('MOD_YSTIDES_WARNING_' . strtoupper(str_replace('-', '_', $headerWarningIcon))); ?>">
						</a>
					<?php endif; ?>
				</div>
				<button type="button" class="btn btn-outline-secondary btn-sm rounded-circle" data-bs-toggle="collapse"
					data-bs-target=".multi-collapse" aria-controls="<?php echo $infoId; ?>" aria-expanded="false"
					aria-label="<?php echo Text::_('MOD_YSTIDES_INFO'); ?>"><i class="fa fa-circle-info"></i></button>
			</div>
			<table class="table table-striped mod-ystides-table mb-0">
				<thead>
					<tr>
						<th scope="col" class="mod-ystides-table-subheader-col1"
							title="<?php echo Text::_('MOD_YSTIDES_HEADING_DATE_DESC'); ?>">
							<?php echo Text::_('MOD_YSTIDES_HEADING_DATE'); ?>
						</th>
						<th scope="col" class="mod-ystides-table-subheader-col2"
							title="<?php echo Text::_('MOD_YSTIDES_HEADING_TIME_DESC'); ?>">
							<?php echo Text::_('MOD_YSTIDES_HEADING_TIME'); ?>
						</th>
						<th scope="col" class="mod-ystides-table-subheader-col3"
							title="<?php echo Text::_('MOD_YSTIDES_HEADING_HEIGHT_DESC'); ?>">
							<?php echo Text::_('MOD_YSTIDES_HEADING_HEIGHT'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($rowsData)): ?>
						<tr class="mod-ystides-empty">
							<td colspan="3"><?php echo Text::_('MOD_YSTIDES_NO_DATA'); ?></td>
						</tr>
					<?php else: ?>
						<?php $prevMeanD = null; ?>
						<?php foreach ($rowsData as $row): ?>
							<?php
							$coefValue = $row['coef'];
							$coefLabel = '';
							$coefColor = '';

							if ($coefValue !== null) {
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
								<td class="mod-ystides-table-data-col1">
									<?php if ($prevMeanD !== $row['meand']): ?>
										<?php if (!empty($row['moonPhase'])): ?>
											<img src="/media/mod_ystides/images/moon-<?php echo $row['moonPhase']; ?>-details.svg"
												width="11" style="margin-top:-3px"
												title="<?php echo Text::_('MOD_YSTIDES_MOON_' . strtoupper($row['moonPhase'])); ?>"
												alt="<?php echo Text::_('MOD_YSTIDES_MOON_' . strtoupper($row['moonPhase'])); ?>">
										<?php else: ?>
											<!-- Empty GIF as space holder for alignment -->
											<img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" width="11" />
										<?php endif; ?>
										<span><?php echo HTMLHelper::_('date', $row['meandt'], 'j M', 'UTC'); ?></span>
										<?php if (!empty($row['warningIcon'])): ?>
											<a href="https://www.met.ie/warnings-today.html" target="_blank" class="ystides-warning-icon">
												<img src="/media/mod_ystides/images/warning-<?php echo htmlspecialchars($row['warningIcon'], ENT_QUOTES, 'UTF-8'); ?>@2x.png"
													width="12" height="12" style="margin-top:-2px"
													title="<?php echo Text::_('MOD_YSTIDES_WARNING_' . strtoupper(str_replace('-', '_', $row['warningIcon']))); ?>"
													alt="<?php echo Text::_('MOD_YSTIDES_WARNING_' . strtoupper(str_replace('-', '_', $row['warningIcon']))); ?>">
											</a>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<td class="mod-ystides-table-data-col2"
									title="<?php echo htmlspecialchars($row['titledt'], ENT_QUOTES, 'UTF-8'); ?>">
									<?php echo str_replace(' ', '&nbsp;', HTMLHelper::_('date', $row['meandt'], 'H:i', 'UTC')); ?>
								</td>
								<td class="mod-ystides-table-data-col3"
									title="<?php echo htmlspecialchars($row['tidehint'], ENT_QUOTES, 'UTF-8'); ?>">
									<span><?php echo $row['wlm']; ?></span>
									<?php if ($coefValue !== null): ?>
										<div class="ystides-coeff ystides-coeff-value-<?php echo $coefColor; ?>"
											title="<?php echo htmlspecialchars($coefLabel, ENT_QUOTES, 'UTF-8'); ?>">
											<?php echo (int) $coefValue; ?>
										</div>
									<?php endif; ?>
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
					<p><?php echo Text::_('MOD_YSTIDES_INFO_7'); ?></p>
					<p><?php echo Text::_('MOD_YSTIDES_INFO_8'); ?></p>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>