<?php
/**
 * @var string $range
 * @var array $chart   ['labels' => [...], 'values' => [...], 'total' => float]
 * @var array $summary
 * @var array $revenueByPlan
 */
$rangeLabels = [
    'hour'  => 'Today by Hour',
    'day'   => 'Last 30 Days',
    'week'  => 'Last 12 Weeks',
    'month' => 'Last 12 Months',
    'year'  => 'Last 5 Years',
];
$maxMix = 0;
foreach ($revenueByPlan as $row) {
    $maxMix = max($maxMix, (float) $row['total']);
}
?>
<div class="dash-head">
  <div><h3>Sales Overview</h3><p>Revenue across the saloon — filter by hour, day, week, month, or year.</p></div>
</div>

<div class="kpi-grid">
  <div class="dcard"><span class="lbl">Today</span><div class="big"><?= money($summary['today']) ?></div></div>
  <div class="dcard"><span class="lbl">This Month</span><div class="big"><?= money($summary['this_month']) ?></div></div>
  <div class="dcard"><span class="lbl">This Year</span><div class="big"><?= money($summary['this_year']) ?></div></div>
  <div class="dcard"><span class="lbl">All-Time Revenue</span><div class="big"><?= money($summary['all_time']) ?></div><div class="sub"><?= (int) $summary['transaction_count'] ?> transactions</div></div>
  <div class="dcard"><span class="lbl">Active Members</span><div class="big"><?= (int) $summary['active_members'] ?></div><div class="sub"><?= (int) $summary['active_plans'] ?> active plans</div></div>
</div>

<div class="range-toggle">
  <?php foreach ($rangeLabels as $key => $label): ?>
    <a href="<?= url('/admin?range=' . $key) ?>"><button class="<?= $range === $key ? 'active' : '' ?>"><?= e($label) ?></button></a>
  <?php endforeach; ?>
</div>

<div class="chart-box">
  <h5>Revenue — <?= e($rangeLabels[$range]) ?></h5>
  <div class="chart-total"><?= money($chart['total']) ?> total</div>
  <div class="chart-canvas-wrap">
    <canvas id="salesChart"></canvas>
  </div>
</div>

<div class="chart-box">
  <h5>Revenue by Plan</h5>
  <?php if (empty($revenueByPlan)): ?>
    <p class="empty-note">No paid subscriptions yet.</p>
  <?php else: ?>
    <div class="mix-list">
      <?php foreach ($revenueByPlan as $row): ?>
        <div class="mix-row">
          <span class="mix-name"><?= e($row['plan_name']) ?></span>
          <div class="mix-bar-track"><div class="mix-bar-fill" style="width:<?= $maxMix > 0 ? round(((float) $row['total'] / $maxMix) * 100) : 0 ?>%;"></div></div>
          <span class="mix-amt"><?= money($row['total']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
  const ctx = document.getElementById('salesChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($chart['labels']) ?>,
      datasets: [{
        label: 'Revenue',
        data: <?= json_encode($chart['values']) ?>,
        backgroundColor: '#C89B3C',
        borderRadius: 4,
        maxBarThickness: 36,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#C9B995', font: { family: "'JetBrains Mono', monospace", size: 10 } } },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(243,232,214,0.08)' },
          ticks: {
            color: '#C9B995',
            font: { family: "'Work Sans', sans-serif", size: 11 },
            callback: (value) => '₦' + Number(value).toLocaleString(),
          },
        },
      },
    },
  });
</script>
