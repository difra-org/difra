function showAddContributor() {

	var divStyle = document.getElementById( 'addContributor' ).style.display;
	if( divStyle!='none' && divStyle!='' ) {
		$( '#addContributor' ).fadeOut( 'fast' );
	} else {
		$( '#addContributor' ).fadeIn( 'fast' );
	}
}

function addContributor() {
	var ulElem = document.getElementById( 'addImageList' );
	ulElem.innerHTML = ulElem.innerHTML + '<tr><th>Выберите изображение</th><td><input name="addImage[]" type="file" /><!--Дополнительное описание картинки<textarea name="addImageDesc[]" style="width: 100%;"></textarea>--></td></tr>';
}

function addUserToWork() {
	var userSelectElem = document.getElementById( 'u' );
	var selectedOption = userSelectElem.options[userSelectElem.selectedIndex].value;
	var userName = document.getElementById( 'userName_' + selectedOption ).value;
	var userRole = document.getElementById( 'userRole_' + selectedOption ).value;
	var usersDiv = document.getElementById( 'usersSelect' );
	var rv = Math.floor( Math.random() * (1000000 - 10000) + 10000 );
	usersDiv.innerHTML = usersDiv.innerHTML
		+ '<tr  id="user_' + selectedOption + '_' + rv + '"><th>' + userName + '</th><td><input name="users[]" type="hidden" value="' + selectedOption
		+ '"/><input type="text" name="userRole[]" value="' + userRole + '"/></td><td><a href="#" class="action delete" onclick="delUserFromWork(\'' + selectedOption + '_' + rv + '\')">удалить</a></td></tr>';
}

function delUserFromWork( id ) {
	$( '#user_' + id ).remove();
}