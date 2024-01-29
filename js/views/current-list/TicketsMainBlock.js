import m from "mithril"
import Filter from "../../models/Filter";
import List from "../../models/List";
import Project from "../../models/Project";
import Tickets from "../../models/Tickets";
import TicketsTable from "./TicketsTable";
import WidgetBox from "./WidgetBox";

let massCloseMode = false

const refresh = () => {
    RefreshButton.loading = true
    Tickets.load(List.getTicketIds(), List.get().projectId).then(() => {
        RefreshButton.loading = false
    });
}

function updateFilterState(name) {
    return (value) => {
        if (value.match(/^\d{4}-\d\d-\d\d$/)) {
            if (Filter.state[name] !== value) {
                Filter.state[name] = value
                refresh()
            }
            return true;
        }
        if (Filter.state[name] !== '') {
            Filter.state[name] = ''
            refresh()
        }
        return false
    };
}

const BlockTitle = {
    view(vnode) {
        const tickets = vnode.attrs.tickets
        return [
            `Tickets listés (${tickets.length})`,
            m('span.actions',
                m(RefreshButton, {num: tickets.length}),
                Project.hasManagerRights() ? m(ToggleMassCloseButton, {num: tickets.length}) : null,
            ),
        ];
    },
}

const RefreshButton = {
    loading: false,
    view(vnode) {
        if (vnode.attrs.num === 0) {
            return null
        }
        return m('button',
            {
                class: 'btn btn-default btn-sm',
                title: "Relire les états de ces tickets sur le serveur",
                type: 'button',
                onclick: refresh,
                disabled: this.loading,
            },
            this.loading ? m('i.fa.fa-spinner') : m('i.fa.fa-refresh')
        )
    },
}

const ToggleMassCloseButton = {
    view(vnode) {
        if (vnode.attrs.num === 0) {
            // No ticket to select
            return null
        }
        return m('button',
            {
                class: 'btn btn-default btn-sm' + (massCloseMode ? ' active' : ''),
                title: "Activer le mode pour sélectionner des tickets et les fermer d'ensemble",
                type: 'button',
                onclick: () => {
                    massCloseMode = !massCloseMode
                },
            },
            m('i.fa.fa-wrench')
        )
    },
}

const MassCloseButton = {
    view(vnode) {
        if (!massCloseMode) {
            return null
        }
        const ids = vnode.attrs.selection
        return m('a',
            {
                class: 'btn btn-danger',
                title: "Ouvrir un formulaire pour fermer simultanément les tickets sélectionnés",
                href: '/plugin.php?page=TicketList/close&ids=' + ids.join(","),
                disabled: (ids.length === 0),
            },
            "Fermer…"
        )
    },
}

const DateInput = {
    oninit(vnode) {
        vnode.state.valid = false
    },
    view(vnode) {
        return m("div.form-group",
            (vnode.state.valid ? { "class": "has-success has-feedbak", style: "position:relative"} : {}),
            [
                m('input.form-control', {placeholder: 'YYYY-MM-DD', size: "11", oninput: function() {
                    vnode.state.valid = vnode.attrs.oninput(this.value)
                }}),
                m('span.glyphicon.glyphicon-ok.form-control-feedback' + (vnode.state.valid ? "" : ".hidden"), {"aria-hidden": "true"}),
            ]
        )
    },
}

const TimeFilter = {
    view() {
        return m('details', {style: "margin-top: 1ex"}, [
            m('summary', {style: "display: list-item"}, "Filtrer par dates"),
            m('div.form-inline',
                "De ",
                m(DateInput, {oninput: updateFilterState("start")}),
                " à ",
                m(DateInput, {oninput: updateFilterState("end")}),
            ),
        ]);
    },
}

const TimeSpent = {
    view() {
        const timeSpent = Tickets.getTimeSpent()
        return m('div',
            `Temps total consacré à ces tickets : ${timeSpent.time}`,
            (timeSpent.minutes > 0 ? m(TimeSpentSinceRelease, {release: timeSpent.release}) : null),
            m(TimeFilter),
        );
    },
}

const TimeSpentSinceRelease = {
    view(vnode) {
        const release = vnode.attrs.release
        if (release === null || release.name === '') {
            return null
        }
        return m('span', [
            " dont ",
            m('strong', release.timeSinceRelease),
            " depuis la livraison ",
            m('a', {href: `/changelog_page.php?version_id=${release.id}`}, release.name),
        ]);
    },
}

export default {
    selection: [],
    oninit() {
        this.selection = []
    },
    view() {
        const tickets = Tickets.get()
        return m(WidgetBox, {
                id: 'issues-main-table',
                class: 'tickets-block',
                title: m(BlockTitle, {tickets}),
                footer: m(TimeSpent),
            },
            [
                m(TicketsTable, {
                    tickets: tickets,
                    selectable: massCloseMode ? this.selection : null,
                }),
                m('div.actions', {style: "text-align: right"},
                    m(MassCloseButton, {selection: this.selection}),
                ),
            ]
        );
    },
}
