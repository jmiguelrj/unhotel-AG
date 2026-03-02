// Quill pre-configuration
(function() {
	// configure Quill to use inline styles rather than classes
	
	var AlignClass = Quill.import('attributors/class/align');
	Quill.register(AlignClass, true);

	var BackgroundClass = Quill.import('attributors/class/background');
	Quill.register(BackgroundClass, true);

	var ColorClass = Quill.import('attributors/class/color');
	Quill.register(ColorClass, true);

	var FontClass = Quill.import('attributors/class/font');
	Quill.register(FontClass, true);

	var SizeClass = Quill.import('attributors/class/size');
	Quill.register(SizeClass, true);

	var AlignStyle = Quill.import('attributors/style/align');
	Quill.register(AlignStyle, true);

	var BackgroundStyle = Quill.import('attributors/style/background');
	Quill.register(BackgroundStyle, true);

	var ColorStyle = Quill.import('attributors/style/color');
	Quill.register(ColorStyle, true);

	var SizeStyle = Quill.import('attributors/style/size');
	Quill.register(SizeStyle, true);

	var FontStyle = Quill.import('attributors/style/font');
	Quill.register(FontStyle, true);

	// set additional fonts
	var Font = Quill.import('formats/font');
	Font.whitelist = ["sans-serif","arial","courier","garamond","tahoma","times-new-roman","verdana","inconsolata","sailec-light","monospace"];
	Quill.register(Font, true);

	// register custom Blot for special tags
	var Inline = Quill.import('blots/inline');
	class Specialtag extends Inline {
		static create(value) {
			var node = super.create(value);
			if (value) {
				node.setAttribute('class', value);
			}
			return node;
		}

		static formats(domNode) {
			return domNode.getAttribute("class");
		}

		format(name, value) {
			if (name !== this.statics.blotName || !value) {
				return super.format(name, value);
			}
			if (value) {
				this.domNode.setAttribute('class', value);
			}
		}
	}
	Specialtag.blotName = 'specialtag';
	Specialtag.tagName = 'strong';
	Quill.register(Specialtag);

	// register bold tag names in the proper order to avoid conflicts
	var Bold = Quill.import('formats/bold');
	Bold.tagName = ['B', 'STRONG'];
	Quill.register(Bold, true);

	// register custom Blot for mail-wrapper
	var BlockEmbed = Quill.import('blots/block/embed');
	class MailWrapper extends BlockEmbed { }
	MailWrapper.blotName = 'mailwrapper';
	MailWrapper.className = 'vbo-editor-hl-mailwrapper';
	MailWrapper.tagName = 'hr';
	Quill.register(MailWrapper);

	// register custom Blot for preview
	class Preview extends Inline { }
	Preview.blotName = 'preview';
	Preview.tagName = 'span';
	Quill.register(Preview);

	// register custom icons for mail-wrapper and preview
	var icons = Quill.import('ui/icons');
	icons['mailwrapper'] = '<i class="fas fa-minus-square" title="Insert content wrapper"></i>';
	icons['preview'] = '<i class="fas fa-eye" title="Preview"></i>';
	icons['homelogo'] = '<i class="fas fa-hotel" title="Company Logo"></i>';
	icons['genai'] = 'AI ✨';
})();