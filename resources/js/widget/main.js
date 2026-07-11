import './widget.css';
import { LeadIntakeWidget } from './LeadIntakeWidget';

(function () {
    var s = document.currentScript;
    var tenant = (s && s.getAttribute('data-tenant')) || window.LEADGEN_TENANT;

    // Missed call full-screen mode
    var mc = window.__LEADGEN_MISSED_CALL__;
    if (mc && mc.token) {
        var w2 = new LeadIntakeWidget();
        w2.initMissedCall(mc);
        window.LeadIntakeWidget = { open: function () { w2.open(); }, close: function () { w2.close(); }, toggle: function () { w2.toggle(); }, isOpen: function () { return w2.isOpen; } };
        return;
    }

    if (!tenant) return;

    var w = new LeadIntakeWidget();
    w.init(tenant);

    // CTA triggers
    var els = document.querySelectorAll('[data-leadgen-trigger]');
    for (var i = 0; i < els.length; i++) {
        els[i].addEventListener('click', function (e) { e.preventDefault(); w.open(); });
    }

    window.LeadIntakeWidget = { open: function () { w.open(); }, close: function () { w.close(); }, toggle: function () { w.toggle(); }, isOpen: function () { return w.isOpen; } };
})();
