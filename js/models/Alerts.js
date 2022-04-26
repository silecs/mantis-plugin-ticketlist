import m from "mithril"

const alerts = new Map()

export default {
    add(message, durationMs) {
        if (durationMs === 0) {
            alerts.set(message, 0)
        } else {
            alerts.set(message, durationMs + (alerts.get(message) ?? 0))
            setTimeout(() => {
                const t = (alerts.get(message) ?? 0) - durationMs
                if (t <= 0) {
                    alerts.delete(message)
                    m.redraw()
                } else {
                    alerts.set(message, t)
                }
            }, durationMs);
        }
    },
    get() {
        return Array.from(alerts.keys())
    },
    remove(message) {
        alerts.delete(message)
    }
}
