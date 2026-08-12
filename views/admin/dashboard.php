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

<div class="admin-topbar">
  <div class="range-toggle" style="margin-bottom:0;">
    <?php foreach ($rangeLabels as $key => $label): ?>
      <a href="<?= url('/admin?range=' . $key) ?>"><button class="<?= $range === $key ? 'active' : '' ?>"><?= e($label) ?></button></a>
    <?php endforeach; ?>
  </div>
  <div class="range-toggle" id="chartTypeToggle" style="margin-bottom:0;">
    <button type="button" class="active" data-type="bar">Bar</button>
    <button type="button" data-type="line">Line</button>
  </div>
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
    <div class="chart-canvas-wrap" style="height:280px; max-width:420px; margin:0 auto;">
      <canvas id="planPieChart"></canvas>
    </div>
  <?php endif; ?>
</div>

<script>
  const revenueData = {
    labels: <?= json_encode($chart['labels']) ?>,
    values: <?= json_encode($chart['values']) ?>,
  };

  const ctx = document.getElementById('salesChart').getContext('2d');
  const revenueChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: revenueData.labels,
      datasets: [{
        label: 'Revenue',
        data: revenueData.values,
        backgroundColor: 'rgba(200,155,60,0.85)',
        borderColor: '#C89B3C',
        borderWidth: 2,
        borderRadius: 4,
        maxBarThickness: 36,
        tension: 0.35,
        fill: true,
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

  document.querySelectorAll('#chartTypeToggle button').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#chartTypeToggle button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const newType = btn.dataset.type;
      revenueChart.config.type = newType;
      revenueChart.data.datasets[0].backgroundColor = newType === 'line' ? 'rgba(200,155,60,0.15)' : 'rgba(200,155,60,0.85)';
      revenueChart.update();
    });
  });

  <?php if (!empty($revenueByPlan)): ?>
  const planCtx = document.getElementById('planPieChart').getContext('2d');
  new Chart(planCtx, {
    type: 'pie',
    data: {
      labels: <?= json_encode(array_column($revenueByPlan, 'plan_name')) ?>,
      datasets: [{
        data: <?= json_encode(array_map('floatval', array_column($revenueByPlan, 'total'))) ?>,
        backgroundColor: ['#C89B3C', '#9C3A44', '#6E9272', '#4A6C8C', '#E7C579'],
        borderColor: '#1B1410',
        borderWidth: 2,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: '#C9B995', font: { family: "'Work Sans', sans-serif", size: 12 }, padding: 14 },
        },
        tooltip: {
          callbacks: { label: (item) => item.label + ': ₦' + Number(item.raw).toLocaleString() },
        },
      },
    },
  });
  <?php endif; ?>
</script>
