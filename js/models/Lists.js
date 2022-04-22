import m from "mithril"

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
            alert(`Erreur en lisant l'api /list (project ${projectId})`)
        }).finally(function() {
            loading = null;
        });
        return loading
    }
}
