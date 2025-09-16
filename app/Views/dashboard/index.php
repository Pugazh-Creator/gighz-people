<?php
$present_days = '';
$absent_days = '';
$sortfall = '';
foreach ($attendance as $userId => $row) {
    $present_days = $row['presentDays'];
    $absent_days = $row['absentDays'];
    $sortfall = $row['sortfall'];
}
?>
<div class="dashboard">
    <div class="top">
        <div class="attendance">
            <div class="attendance-child ac1">
                <div class="eff1">
                    <span>
                        Present <br> 25
                    </span>
                    <span>
                        <img src="<?= base_url("asset/icons/working-pbg.png")?>" alt="">
                    </span>
                </div>
                <div class="eff1">box2</div>
                <div class="eff1">box3</div>
            </div>
            <div class="attendance-child ac2">
                <div class="eff1">box1</div>
                <div class="eff1">box2</div>
            </div>
        </div>
        <div>

        </div>
    </div>
</div>