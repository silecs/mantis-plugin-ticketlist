import m from "mithril"
import Alerts from "./Alerts"

let content = []

let loading = null

export default {
    get() {
        return content;
    },
    isLoading() {
        return loading !== null;
    },
    load(projectId) {
        if (loading !== null) {
            return loading;
        }
        loading = m.request({
            method: "GET",
            url: `/plugin.php`,
            params: {
                page: "TicketList/api",
                action: "list",
                projectId,
            },
            withCredentials: true,
        }).then(function(result) {
            content = result ?? [];
            return content;
        }).catch(function() {
            Alerts.add(`Erreur en lisant l'api /list (project ${projectId})`, 0)
        }).finally(function() {
            loading = null;
        });
        return loading
    }
}
