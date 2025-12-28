<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_ystides
 *
 * @copyright   (C) 2025 YSTides
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$moduleClassSfx = isset($moduleclass_sfx) ? $moduleclass_sfx : '';
$stationHeader  = $stationName ?? '';
$rangeStart     = $dateRangeStart ?? '';
$rangeEnd       = $dateRangeEnd ?? '';
$dbErrorMessage = $dbError ?? '';
$fetchErrorMessage = $fetchError ?? '';
$rowsData       = $rows ?? [];
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
	<table class="table table-striped mod-ystides-table">
		<thead>
			<tr>
				<th colspan="3" class="mod-ystides-table-header">
					<?php echo Text::sprintf('MOD_YSTIDES_HEADER_DESC', htmlspecialchars($stationHeader, ENT_QUOTES, 'UTF-8')); ?>
				</th>
			</tr>
			<tr>
				<th colspan="3" class="mod-ystides-table-subheader">
					<?php echo Text::sprintf('MOD_YSTIDES_DATE_RANGE', htmlspecialchars($rangeStart, ENT_QUOTES, 'UTF-8'), htmlspecialchars($rangeEnd, ENT_QUOTES, 'UTF-8')); ?>
				</th>
			</tr>
			<tr>
				<th scope="col" class="mod-ystides-table-subheader-col1"><?php echo Text::_('MOD_YSTIDES_HEADING_TIME'); ?></th>
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
						<td class="mod-ystides-table-data-col1" title="<?php echo htmlspecialchars($row['tooltip'], ENT_QUOTES, 'UTF-8'); ?>">
							<?php echo htmlspecialchars($row['time'], ENT_QUOTES, 'UTF-8'); ?>
						</td>
						<td class="mod-ystides-table-data-col2" title="<?php echo htmlspecialchars($row['hint'], ENT_QUOTES, 'UTF-8'); ?>">
							<?php echo htmlspecialchars($row['symbol'] . ' ' . $row['wlm'], ENT_QUOTES, 'UTF-8'); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>
