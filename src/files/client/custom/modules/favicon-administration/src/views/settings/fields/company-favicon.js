define(['views/fields/file'], Dep => {
	return class extends Dep {
		setup() {
			super.setup();

			if (this.mode === 'edit') {
				this.listenTo(this.model, `change:${this.name}Id`, () => {
					this.updateFavicon();
				});
			}
		}

		updateFavicon() {
			const attachmentId = this.model.get(this.idName) ?? null;

			Espo.Ajax.postRequest('FaviconAdministration/action/updateFavicon', {
				attachmentId: attachmentId
			}).then((response) => {
				if (response.success) {
					Espo.Ui.success('Favicon updated');
					this.model.save();
				}
			});
		}
	};
});