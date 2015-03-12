(function() {
  'use strict';
  
  var tpl = '<div class="filespart"><div class="filespart-breadcrumbs" data-breadcrumbs-container></div><div class="filespart-files"><div class="filespart-files-inside" data-items-container></div></div>';
  var itemTpl = '<label class="filespart-files-item" data-folder="{folder}" data-path="{path}"><span class="filespart-files-item-inside"><span class="filespart-files-icon {iconClasses}" {iconAttrs}></span><span class="filespart-files-name" title="{name}">{name}</a><input type="checkbox" data-path="{path}" {checked} /><span class="filespart-files-mark"></span></span></label>';
  var breadcrumbTpl = '<a class="filespart-breadcrumbs-item" nohref nofollow data-path="{path}">{name}</a>';
  var body = document.getElementsByTagName('BODY')[0];
  
  function findParentNodeWithAttr(el, attrName) {
    var node = el;
    while (node) {
      if (node && node.hasAttribute(attrName)) {
        return node;
      }
      node = node.parentNode;
    }
    return null;
  }
  
  function findSelectedIndex(instance, itemPath) {
    var i,ln;
    for (i=0,ln=instance.selected.length; i<ln; i++) {
      if (instance.selected[i].path === itemPath) {
        return i;
      }
    }
    return -1;
  }
  
  function render(instance) {
    // render container if needed
    if (! instance.containerRendered) {
      if (! instance.container) {
        instance.container = document.createElement('DIV');
        body.appendChild(instance.container);
      }
      instance.container.insertAdjacentHTML('afterbegin', tpl);
      instance.itemsContainer = instance.container.querySelector('[data-items-container]');
      instance.breadcrumbsContainer = instance.container.querySelector('[data-breadcrumbs-container]');
      instance.breadcrumbsContainer.onclick = function(event) {
        event.stopPropagation();
        navigateByBreadcrumb(instance, findParentNodeWithAttr(event.target, 'data-path'));
      };
      instance.itemsContainer.onclick = function(event) {
        if (event.target.hasAttribute('data-path')) {
          event.stopPropagation();
          if (navigateByItem(instance, findParentNodeWithAttr(event.target, 'data-folder')) === false) {
            event.preventDefault();
          }
        }
      };
      instance.containerRendered = true;
    }
    instance.itemsContainer.parentNode.className = 'filespart-files filespart-files-' + instance.viewMode;
    // render items
    instance.itemsContainer.innerHTML = '';
    var el,i,ln,iconClasses,iconStyles,isChecked;
    var items = instance.items;
    if (typeof instance.sortFn === 'function') {
      items = items.sort(instance.sortFn);
    }
    for (i=0,ln=items.length; i<ln; i++) {
      if (typeof instance.filterFn === 'function' && instance.filterFn(items[i]) === false) {
        continue;
      }
      el = document.createElement('DIV');
      instance.itemsContainer.appendChild(el);
      iconClasses = typeof items[i].iconClasses === 'undefined' ? '': items[i].iconClasses;
      iconStyles = '';
      if (typeof items[i].iconImage !== 'undefined' && items[i].iconImage !== null) {
        iconStyles += 'background-image:url(\''+items[i].iconImage+'\');';
        iconClasses += ' filespart-files-icon-havebg ';
      }
      if (typeof items[i].iconColor !== 'undefined') {
        iconStyles += 'background-color:'+items[i].iconColor+';';
      }
      iconStyles = iconStyles === '' ? '': ' style="'+iconStyles+'" ';
      el.outerHTML = itemTpl
        .replace(/{name}/g, items[i].name)
        .replace(/{path}/g, items[i].path)
        .replace(/{folder}/g, items[i].folder)
        .replace(/{iconClasses}/g, iconClasses)
        .replace(/{checked}/g, findSelectedIndex(instance, items[i].path) !== -1 ? 'checked': '')
        .replace(/{iconAttrs}/g, iconStyles);
    }
    // render breadcrumbs
    instance.breadcrumbsContainer.innerHTML = '';
    var pathItems = instance.path.split('/');
    if (pathItems[0] !== '') {
      pathItems.unshift('');
    }
    var name, partialPath = [];
    for (i=0,ln=pathItems.length; i<ln; i++) {
      el = document.createElement('SPAN');
      instance.breadcrumbsContainer.appendChild(el);
      name = (pathItems[i] === '' ? 'Home': pathItems[i]);
      pathItems[i] !== '' && partialPath.push(pathItems[i]);
      el.outerHTML = breadcrumbTpl
        .replace(/{path}/g, partialPath.join('/'))
        .replace(/{name}/g, name);
    }
  }
  
  function navigateByPath(instance, newPath) {
    newPath = typeof newPath === 'undefined' ? instance.path: String(newPath);
    var i,ln,foundedItem=null;
    for (i=0,ln=instance.items.length; i<ln; i++) {
      if (instance.items[i].path === newPath) {
        foundedItem = instance.items[i];
        break;
      }
    }
    if (typeof instance.navigateCb === 'function') {
      instance.navigateCb(function(newItems) {
        if (newItems instanceof Array) {
          instance.items = newItems;
          instance.path = newPath;
          instance.itemsContainer.parentNode.scrollTop = 0;
          render(instance);
        }
      }, newPath, foundedItem);
    }
  }
  
  function navigateByBreadcrumb(instance, target) {
    navigateByPath(instance, target.getAttribute('data-path'));
  }
  
  function navigateByItem(instance, target) {
    var targetFolder = target.getAttribute('data-folder') === 'true' ? true: false;
    var targetPath = target.getAttribute('data-path');
    if (targetFolder) {
      navigateByPath(instance, targetPath);
    } else {
      var selectedIndex = findSelectedIndex(instance, target.getAttribute('data-path'));
      if (selectedIndex !== -1) {
        instance.selected.splice(selectedIndex, 1);
      }
      var inputs = instance.itemsContainer.getElementsByTagName('INPUT');
      var i,j,lni,lnj,triggeredItem;
      for (i=0,lni=inputs.length; i<lni; i++) {
        if (inputs[i].hasAttribute('data-path') && inputs[i].checked) {
          for (j=0,lnj=instance.items.length; j<lnj; j++) {
            if (inputs[i].getAttribute('data-path') === instance.items[j].path) {
              if (findSelectedIndex(instance, inputs[i].getAttribute('data-path')) === -1) {
                instance.selected.push(instance.items[j]);
              }
              break;
            }
          }
        }
      }
      for (j=0,lnj=instance.items.length; j<lnj; j++) {
        if (targetPath === instance.items[j].path) {
          triggeredItem = instance.items[j];
        }
      }
      if (typeof instance.selectCb === 'function') {
        return instance.selectCb(triggeredItem, instance.selected);
      }
    }
  }

  function defaultSortFn(itemA, itemB) {
    if (itemA.folder && ! itemB.folder) {
      return -1;
    } else if (!itemA.folder && itemB.folder) {
      return 1;
    } else if (itemA.name > itemB.name) {
      return 1;
    } else if (itemA.name < itemB.name) {
      return -1;
    }
    return 0;
  }
  
  var Cls = function(o) {
    // new options list
    var options = {
      selectCb: null,
      navigateCb: null,
      items: [],
      selected: [],
      path: '',
      container: null,
      itemsContainer: null,
      breadcrumbsContainer: null,
      containerRendered: false,
      viewMode: 'list',
      filterFn: null,
      sortFn: defaultSortFn
    };
    // assign options
    for (var k in options) {
      this[k] = typeof o === 'object' && typeof o[k] !== 'undefined' ? o[k]: options[k];
    }
    // render it
    render(this);
    return this;
  };
  
  Cls.prototype = {
    onSelect: function(cb) {
      if (typeof cb === 'function') {
        this.selectCb = cb;
      }
      return this;
    },
    onNavigate: function(cb) {
      if (typeof cb === 'function') {
        this.navigateCb = cb;
      }
      return this;
    },
    navigate: function(newPath) {
      navigateByPath(this, newPath);
      return this;
    },
    setViewMode: function(viewMode) {
      this.viewMode = String(viewMode);
      render(this);
      return this;
    },
    setItems: function(items) {
      if (items instanceof Array) {
        this.items = items;
        render(this);
      }
      return this;
    },
    setFilterFn: function(fn) {
      if (typeof fn === 'function') {
        this.itemsFilterFn = fn;
      } else if (fn === null) {
        this.itemsFilterFn = null;
      }
      render(this);
      return this;
    },
    setSortFn: function(fn) {
      if (typeof fn === 'function') {
        this.sortFn = fn;
      } else if (fn === null) {
        this.sortFn = null;
      }
      render(this);
      return this;
    },
    deselectAll: function() {
      this.selected = [];
      render(this);
      return this;
    },
    getSelected: function() {
      return this.selected;
    }
  };
  
  window.Picker = Cls;
})();
