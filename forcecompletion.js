
M.force_completion = {};

M.force_completion.init = function(Y) {

    var handle_success = function(id, o, args) {

    	if (o.responseText != 'OK') {
            alert('An error occurred when attempting to save forced state.\n\n('+o.responseText+'.)'); //TODO: localize
        } else {
            var current = args.state.get('value');
            var modulename = args.modulename.get('value');
            if (current == 1) {
            	//alert('State set to complete/forced.');
                var altstr = modulename + ' - ' + M.str.report_completion['completion:forcedpending'];
                var titlestr = modulename + ' - ' + M.str.report_completion['completion:forcedpending'];
                args.state.set('value', 0);
                args.image.set('alt', altstr);
                args.image.set('title', titlestr);
                args.forceicon.setStyle('display','block');
            } else {
            	//alert('Forced complete removed.');
                var altstr = modulename + ' - ' + M.str.report_completion['completion:statuspending'];
                var titlestr = modulename + ' - ' + M.str.report_completion['completion:statuspending'];
                args.state.set('value', 1);
                args.image.set('alt', altstr);
                args.image.set('title', titlestr);
                args.forceicon.setStyle('display','none');
            }
        }

        args.ajax.remove();
    };

    var handle_failure = function(id, o, args) {
        alert('An error occurred when attempting to save forced state.\n\n('+o.responseText+'.)'); //TODO: localize
        args.ajax.remove();
    };

    var toggle = function(e) {
        e.preventDefault();

        var form = e.target;
        var cmid = 0;
        var forcecompletionstate = 0;
        var state = null;
        var image = null;
        var modulename = null;

        var inputs = Y.Node.getDOMNode(form).getElementsByTagName('input');
        for (var i=0; i<inputs.length; i++) {
            switch (inputs[i].name) {
                 case 'id':
                     cmid = inputs[i].value;
                     break;
                 case 'criteriaid':
                	 criteriaid = inputs[i].value;
                     break;
                 case 'userid':
                     userid = inputs[i].value;
                     break;
                 case 'forceicon':
                	 forceicon = Y.one(inputs[i]);
                     break;
                 case 'forcecompletionstate':
                	 forcecompletionstate = inputs[i].value;
                     state = Y.one(inputs[i]);
                     break;
                 case 'modulename':
                     modulename = Y.one(inputs[i]);
                     break;
            }
            if (inputs[i].type == 'image') {
                image = Y.one(inputs[i]);
            }
        }

        // start spinning the ajax indicator
        var ajax = Y.Node.create('<div class="ajaxworking" />');
        form.append(ajax);

        var cfg = {
            method: "POST",
            data: 'id='+cmid+'&criteriaid='+criteriaid+'&userid='+userid+'&forcecompletionstate='+forcecompletionstate+'&fromajax=1&sesskey='+M.cfg.sesskey,
            on: {
                success: handle_success,
                failure: handle_failure
            },
            arguments: {state: state, image: image, ajax: ajax, modulename: modulename, forceicon: forceicon}
        };

        Y.use('io-base', function(Y) {
            Y.io(M.cfg.wwwroot+'/report/completion/toggleforcecompletion.php', cfg);
        });
    };

    // register submit handlers on manual tick completion forms
    Y.all('form.toggleforcecompletion').each(function(form) {
        if (!form.hasClass('preventjs')) {
            Y.on('submit', toggle, form);
        }
    });

    // hide the help if there are no completion toggles or icons
    var help = Y.one('#completionprogressid');
    if (help && !(Y.one('form.toggleforcecompletion') || Y.one('.autocompletion'))) {
        help.setStyle('display', 'none');
    }
};


