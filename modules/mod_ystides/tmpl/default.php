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

$moduleClassSfx = isset($moduleclass_sfx) ? $moduleclass_sfx : '';
$stationHeader  = $stationName ?? '';
$dbErrorMessage = $dbError ?? '';
$fetchErrorMessage = $fetchError ?? '';
$rowsData       = $rows ?? [];
$moduleId       = isset($module) ? (int) $module->id : rand(1000, 9999);
$mainId         = 'ystides-main-' . $moduleId;
$infoId         = 'ystides-info-' . $moduleId;
?>
<div class="mod-ystides<?php echo htmlspecialchars($moduleClassSfx, ENT_QUOTES, 'UTF-8'); ?>">
	<?php if ($dbErrorMessage !== '') : ?>
		<div class="alert alert-warning">
			<?php echo htmlspecialchars($dbErrorMessage, ENT_QUOTES, 'UTF-8'); ?>
		</div>
	<?php elseif ($fetchErrorMessage !== '') : ?>
		<div class="alert alert-warning">
			<?php echo htmlspecialchars($fetchErrorMessage, ENT_QUOTES, 'UTF-8'); ?>
		</div>
	<?php else : ?>
	<div class="mod-ystides__wrap collapse multi-collapse show" id="<?php echo $mainId; ?>">
		<div class="d-flex align-items-center justify-content-between mb-2">
			<div class="fw-semibold">
				<?php echo Text::sprintf('MOD_YSTIDES_HEADER_DESC', htmlspecialchars($stationHeader, ENT_QUOTES, 'UTF-8')); ?>
			</div>
			<button
				type="button"
				class="btn btn-outline-secondary btn-sm rounded-circle"
				data-bs-toggle="collapse"
				data-bs-target=".multi-collapse"
				aria-controls="<?php echo $infoId; ?>"
				aria-expanded="false"
				aria-label="<?php echo Text::_('MOD_YSTIDES_INFO'); ?>"><i class="fa fa-circle-info"></i></button>
		</div>
		<table class="table table-striped mod-ystides-table mb-0">
			<thead>
				<tr>
					<th colspan="2" scope="col" class="mod-ystides-table-subheader-col1"><?php echo Text::_('MOD_YSTIDES_HEADING_TIME'); ?></th>
					<th scope="col" class="mod-ystides-table-subheader-col2"><?php echo Text::_('MOD_YSTIDES_HEADING_WLM'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($rowsData)) : ?>
					<tr class="mod-ystides-empty">
						<td colspan="3"><?php echo Text::_('MOD_YSTIDES_NO_DATA'); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ($rowsData as $row) : ?>
						<tr>
							<td class="mod-ystides-table-data-col1">
								<?php echo htmlspecialchars($row['startd'], ENT_QUOTES, 'UTF-8'); ?>
								<?php if ($row['startd'] !== $row['endd']) : ?>
								  <br> <?php echo htmlspecialchars($row['endd'], ENT_QUOTES, 'UTF-8'); ?>
								<?php endif; ?>
							</td>
							<td class="mod-ystides-table-data-col1">
								<?php echo htmlspecialchars($row['startt'], ENT_QUOTES, 'UTF-8'); ?> <br>
								<?php echo htmlspecialchars($row['endt'], ENT_QUOTES, 'UTF-8'); ?>
							</td>
							<td class="mod-ystides-table-data-col2">
								<?php echo htmlspecialchars($row['symbol'] . ' ' . $row['wlm'], ENT_QUOTES, 'UTF-8'); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<div class="mod-ystides__wrap collapse multi-collapse" id="<?php echo $infoId; ?>" data-bs-parent=".mod-ystides">
		<div class="d-flex align-items-center justify-content-between mb-2">
			<button
				type="button"
				class="btn btn-outline-secondary btn-sm rounded-circle"
				data-bs-toggle="collapse"
				data-bs-target=".multi-collapse"
				aria-controls="<?php echo $mainId; ?>"
				aria-expanded="false"
				aria-label="<?php echo Text::_('MOD_YSTIDES_BACK'); ?>">&larr;</button>
			<div class="fw-semibold"><?php echo Text::_('MOD_YSTIDES_INFO'); ?></div>
		</div>
		<div class="card">
			<div class="card-body">
				<?php echo htmlspecialchars(Text::_('MOD_YSTIDES_INFO_TEXT'), ENT_QUOTES, 'UTF-8'); ?>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
