<script setup>
defineProps({
    items: {
        type: Array,
        required: true,
    },
})
</script>

<template>
    <section class="dashboard-panel revenue-panel">
        <div class="panel-header">
            <h2>Doanh thu 7 ngày gần đây</h2>

            <select class="form-select form-select-sm period-select" aria-label="Lọc thời gian">
                <option>7 ngày gần đây</option>
                <option>30 ngày gần đây</option>
                <option>Tháng này</option>
            </select>
        </div>

        <div class="chart-wrap">
            <div class="chart-y-axis">
                <span>30M đ</span>
                <span>24M đ</span>
                <span>18M đ</span>
                <span>12M đ</span>
                <span>6M đ</span>
                <span>0 đ</span>
            </div>

            <div class="chart-body">
                <div class="grid-lines">
                    <span v-for="line in 6" :key="line"></span>
                </div>

                <div class="bars">
                    <div v-for="item in items" :key="item.day" class="bar-item">
                        <strong>{{ item.label }}</strong>

                        <div class="bar-rail">
                            <span class="bar-fill" :style="{ height: `${item.percent}%` }"></span>
                        </div>

                        <small>{{ item.day }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-legend">
            <span></span>
            Doanh thu (đ)
        </div>
    </section>
</template>

<style scoped>
.dashboard-panel {
    height: 100%;
    overflow: hidden;
    background: #ffffff;
    border: 1px solid #e5e9f1;
    border-radius: 10px;
    box-shadow: 0 9px 25px rgba(15, 23, 42, 0.05);
}

.panel-header {
    height: 62px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 0 22px 0 24px;
    border-bottom: 1px solid #edf0f5;
}

.panel-header h2 {
    margin: 0;
    color: #0f172a;
    font-size: 19px;
    font-weight: 800;
}

.period-select {
    width: 162px;
    height: 40px;
    color: #0f172a;
    border-color: #d8dee9;
}

.chart-wrap {
    display: grid;
    grid-template-columns: 44px 1fr;
    gap: 14px;
    padding: 19px 23px 0 22px;
}

.chart-y-axis {
    height: 258px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: flex-end;
    color: #64748b;
    font-size: 14px;
    white-space: nowrap;
}

.chart-body {
    position: relative;
    height: 258px;
    border-left: 1px solid #e5e9f1;
    border-bottom: 1px solid #e5e9f1;
}

.grid-lines {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.grid-lines span {
    height: 1px;
    background: #e9edf4;
}

.bars {
    position: relative;
    z-index: 1;
    height: 100%;
    display: grid;
    grid-template-columns: repeat(7, minmax(30px, 1fr));
    align-items: end;
    gap: clamp(8px, 1.4vw, 18px);
    padding: 0 12px;
}

.bar-item {
    height: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    gap: 9px;
}

.bar-item strong {
    color: #0f172a;
    font-size: 14px;
    font-weight: 800;
}

.bar-rail {
    width: 34px;
    height: 205px;
    display: flex;
    align-items: flex-end;
}

.bar-fill {
    width: 100%;
    min-height: 7px;
    display: block;
    border-radius: 6px 6px 0 0;
    background: linear-gradient(180deg, #1685ff 0%, #006df2 100%);
    box-shadow: 0 8px 18px rgba(0, 109, 242, 0.16);
}

.bar-item small {
    color: #0f172a;
    font-size: 14px;
    font-weight: 700;
}

.chart-legend {
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: #475569;
    font-size: 14px;
}

.chart-legend span {
    width: 17px;
    height: 15px;
    display: inline-block;
    border-radius: 3px;
    background: #0d6efd;
}

@media (max-width: 767.98px) {
    .panel-header {
        height: auto;
        align-items: flex-start;
        flex-direction: column;
        padding: 18px;
    }

    .period-select {
        width: 100%;
    }

    .chart-wrap {
        grid-template-columns: 38px 1fr;
        overflow-x: auto;
    }

    .chart-body {
        min-width: 620px;
    }
}
</style>