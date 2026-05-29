/* Mikhmon — Template Editor (CodeMirror) */
(function () {
  var EDITOR_JS = "./js/editor.min.js";
  var EDITOR_CSS = "./css/editor.min.css";
  var loadPromise = null;

  function absUrl(path) {
    try {
      return new URL(path, window.location.href).toString();
    } catch (e) {
      return path;
    }
  }

  function loadStylesheet(href) {
    var abs = absUrl(href);
    if (document.querySelector('link[rel="stylesheet"][href="' + abs + '"]')) return;
    var link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = abs;
    document.head.appendChild(link);
  }

  function loadScript(src) {
    var abs = absUrl(src);
    if (document.querySelector('script[src="' + abs + '"]')) {
      return typeof CodeMirror !== "undefined"
        ? Promise.resolve()
        : new Promise(function (resolve) {
            var tries = 0;
            (function wait() {
              if (typeof CodeMirror !== "undefined" || tries++ > 50) {
                resolve();
                return;
              }
              setTimeout(wait, 20);
            })();
          });
    }

    return new Promise(function (resolve, reject) {
      var script = document.createElement("script");
      script.src = abs;
      script.async = false;
      script.onload = function () { resolve(); };
      script.onerror = function () { reject(new Error("Failed to load " + src)); };
      document.head.appendChild(script);
    });
  }

  function ensureEditorAssets() {
    if (!loadPromise) {
      loadPromise = Promise.resolve()
        .then(function () {
          loadStylesheet(EDITOR_CSS);
          return loadScript(EDITOR_JS);
        })
        .catch(function () {
          loadPromise = null;
        });
    }
    return loadPromise;
  }

  function textareaHasEditor(textarea) {
    if (!textarea) return false;
    if (textarea.getAttribute("data-cm-ready") === "1") return true;
    var next = textarea.nextElementSibling;
    return !!(next && next.classList && next.classList.contains("CodeMirror"));
  }

  function bindEditorForm(editor) {
    if (!editor) return;

    function syncEditorToTextarea() {
      try {
        editor.save();
      } catch (e) {}
    }

    var form = editor.getTextArea && editor.getTextArea().form;
    if (form && !form.getAttribute("data-cm-bound")) {
      form.setAttribute("data-cm-bound", "1");
      form.addEventListener("submit", syncEditorToTextarea, true);
    }

    try {
      editor.addKeyMap({
        "Ctrl-S": function (cm) {
          syncEditorToTextarea();
          var f = cm && cm.getTextArea && cm.getTextArea().form;
          if (!f) return;
          if (typeof f.requestSubmit === "function") f.requestSubmit();
          else f.submit();
        },
        "Cmd-S": function (cm) {
          syncEditorToTextarea();
          var f = cm && cm.getTextArea && cm.getTextArea().form;
          if (!f) return;
          if (typeof f.requestSubmit === "function") f.requestSubmit();
          else f.submit();
        },
      });
    } catch (e) {}
  }

  function createEditor(textarea) {
    if (typeof CodeMirror === "undefined") return null;

    try {
      if (window.__mikhmonVoucherEditor && window.__mikhmonVoucherEditor.getTextArea) {
        var oldTa = window.__mikhmonVoucherEditor.getTextArea();
        if (oldTa && oldTa !== textarea) {
          try { window.__mikhmonVoucherEditor.toTextArea(); } catch (e) {}
        }
      }
    } catch (e) {}

    var editor = CodeMirror.fromTextArea(textarea, {
      lineNumbers: true,
      matchBrackets: true,
      mode: "application/x-httpd-php",
      indentUnit: 4,
      indentWithTabs: true,
      lineWrapping: true,
      viewportMargin: Infinity,
      matchTags: { bothTags: true },
      extraKeys: { "Ctrl-J": "toMatchingTag" },
    });

    try {
      editor.setOption("theme", "material");
    } catch (e) {}

    textarea.setAttribute("data-cm-ready", "1");
    window.__mikhmonVoucherEditor = editor;
    window.editor = editor;
    bindEditorForm(editor);

    try {
      editor.refresh();
      setTimeout(function () {
        try { editor.refresh(); } catch (e) {}
      }, 0);
    } catch (e) {}

    return editor;
  }

  window.mikhmon_initVoucherEditor = function (rootEl) {
    rootEl = rootEl || document;
    var textarea = rootEl.querySelector
      ? rootEl.querySelector("#editorMikhmon")
      : document.getElementById("editorMikhmon");
    if (!textarea || textareaHasEditor(textarea)) return;

    ensureEditorAssets().then(function () {
      var ta = document.getElementById("editorMikhmon");
      if (!ta || textareaHasEditor(ta)) return;
      createEditor(ta);
    });
  };

  window.mikhmon_syncVoucherEditor = function () {
    try {
      if (window.__mikhmonVoucherEditor && typeof window.__mikhmonVoucherEditor.save === "function") {
        window.__mikhmonVoucherEditor.save();
        return;
      }
      if (window.editor && typeof window.editor.save === "function") {
        window.editor.save();
      }
    } catch (e) {}
  };

  window.mikhmon_isVoucherEditorForm = function (form) {
    try {
      return !!(form && form.getAttribute && form.getAttribute("data-mm-voucher-editor") === "1");
    } catch (e) {
      return false;
    }
  };

  document.addEventListener("submit", function (e) {
    var form = e.target;
    if (!form || form.tagName !== "FORM") return;
    if (typeof mikhmon_isVoucherEditorForm !== "function" || !mikhmon_isVoucherEditorForm(form)) return;
    mikhmon_syncVoucherEditor();
  }, true);
})();
