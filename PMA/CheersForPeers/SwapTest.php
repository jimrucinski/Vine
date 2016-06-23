<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


?>
<script language="JavaScript">
    function listbox_moveacross(sourceID, destID) {
    var src = document.getElementById(sourceID);
    var dest = document.getElementById(destID);
 
    for(var count=0; count < src.options.length; count++) {
 
        if(src.options[count].selected == true) {
                var option = src.options[count];
 
                var newOption = document.createElement("option");
                newOption.value = option.value;
                newOption.text = option.text;
                newOption.selected = true;
                try {
                         dest.add(newOption, null); //Standard
                         src.remove(count, null);
                 }catch(error) {
                         dest.add(newOption); // IE only
                         src.remove(count);
                 }
                count--;
        }
    }
}

function listbox_move(listID, direction) {
 
    var listbox = document.getElementById(listID);
    var selIndex = listbox.selectedIndex;
 
    if(-1 == selIndex) {
        alert("Please select an option to move.");
        return;
    }
 
    var increment = -1;
    if(direction == 'up')
        increment = -1;
    else
        increment = 1;
 
    if((selIndex + increment) < 0 ||
        (selIndex + increment) > (listbox.options.length-1)) {
        return;
    }
 
    var selValue = listbox.options[selIndex].value;
    var selText = listbox.options[selIndex].text;
    listbox.options[selIndex].value = listbox.options[selIndex + increment].value
    listbox.options[selIndex].text = listbox.options[selIndex + increment].text
 
    listbox.options[selIndex + increment].value = selValue;
    listbox.options[selIndex + increment].text = selText;
 
    listbox.selectedIndex = selIndex + increment;
}
</script>
<table>
    <tr>
        <td>
            <SELECT id="s" size="10" multiple>
            <OPTION value="a">Afghanistan</OPTION>
            <OPTION value="b">Bahamas</OPTION>
            <OPTION value="c">Barbados</OPTION>
            <OPTION value="d">Belgium</OPTION>
            <OPTION value="e">Bhutan</OPTION>
            <OPTION value="f">China</OPTION>
            <OPTION value="g">Croatia</OPTION>
            <OPTION value="h">Denmark</OPTION>
            <OPTION value="i">France</OPTION>
            </SELECT>
        </td>
        <td><a href="#" onclick="listbox_moveacross('s', 'd')">&gt;&gt;</a>
        <br/>
            <a href="#" onclick="listbox_moveacross('d', 's')">&lt;&lt;</a></td>
        <td><SELECT id="d" size="10" multiple>
	<OPTION value="a">Afghanistan</OPTION>
	<OPTION value="b">Bahamas</OPTION>
	<OPTION value="c">Barbados</OPTION>
	<OPTION value="d">Belgium</OPTION>
	<OPTION value="e">Bhutan</OPTION>
	<OPTION value="f">China</OPTION>
	<OPTION value="g">Croatia</OPTION>
	<OPTION value="h">Denmark</OPTION>
	<OPTION value="i">France</OPTION>
    </SELECT></td>
    <td>
        Move <a href="#" onclick="listbox_move('d', 'up')">up</a>,
<a href="#" onclick="listbox_move('d', 'down')">down</a>

    </td>
    </tr>
</table>
