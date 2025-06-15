define(['views/settings/record/edit'], Dep => {
	return class extends Dep {
		layoutName = 'alfredSettings';
		saveAndContinueEditingAction = false;
	};
});