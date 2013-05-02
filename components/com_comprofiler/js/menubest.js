/* iubito's menu - http://iubito.free.fr/prog/menu.php - configuration du javascript */


/* true = le menu sera vertical, � gauche.
   false = le menu sera horizontal, en haut. */
var vertical = false;

/* TRES IMPORTANT !
	Il faut mettre ici le nombre de menus, le script n'est pas capable de compter tout
	seul ! :-p Donc si votre code va jusqu'� <li id="menu5"...> il faut mettre 5. */
var nbmenu = 3;		// valeur par defut pour les exemples



/* Centrer le menu ? (true/false).
	Centre horizontalement ou verticalement suivant le mode choisi. */
var centrer_menu = false;

/* On est oblig� de d�finir une largeur pour les menus.
	Pour mettre des menus de largeurs diff�rentes :
	var largeur_menu = new Array(largeur menu1, largeur menu2, largeur menu3...)
	Il faut faire attention � mettre autant de valeurs que de nombre de menu !
	Attention, si vous �tes en menu vertical, mettez une largeur fixe (pas de Array) !
	*/
var largeur_menu = 95;

/* En mode vertical, on a besoin de conna�tre la hauteur de chaque menu.
	M�me si les "cases" ne sont pas dimensionn�es en hauteur.
	Ajustez cette variable si les menus sont trop rapproch�s ou espac�s en vertical.
	Pour mettre des menus de hauteurs diff�rentes :
	var hauteur_menu = new Array(hauteur menu1, hauteur menu2, hauteur menu3...)
	Il faut faire attention � mettre autant de valeurs que de nombre de menu !
	Attention, si vous �tes en menu horizontal, mettez une largeur fixe (pas de Array) !
	*/
var hauteur_menu = 25;

/* En mode horizontal.
	Largeur des sous-menus, pour IE uniquement, les autres navigateurs respectent la largeur
	auto. Pour mettre des sous-menus de largeurs diff�rentes :
	var largeur_sous_menu = new Array(largeur1, largeur2...).
	Il faut faire attention � mettre autant de valeurs que de menus.
	Si un menu n'a pas de sous-menus, il faut mettre quand m�me quelque chose !
	Il est possible de mettre "auto" dans certaines colonnes, � condition de respecter la
	consigne ci-dessus.
	*/
var largeur_sous_menu = 150;

/* Pour les navigateurs connaissant la largeur automatique (s'adapte au contenu), cette
	option (active par d�faut) permet d'avoir une largeur automatique. En cas contraire
	(false), les sous menus auront la largeur largeur_sous_menu. */
var largeur_auto_ssmenu = true;

/* ... pour mettre un peu d'espace entre les menus ! */
var espace_entre_menus = 5;


/* position du menu par rapport � un autre element de la page. en px. */
var cbMenuAnchorReference = "1";		// if 1 defaults to 'conteneurmenu'. if null absolute positions [left_menu, top_menu]
var cbMenuAnchorOffsetx = 0;
var cbMenuAnchorOffsety = 0;

/* position du menu par rapport au haut de l'�cran ou de la page.
	0 = le menu est tout en haut. en px */
var top_menu = 10;

/* En version horizontale.
	position des sous-menus par rapport au haut de l'�cran ou de la page. Il faut pr�voir
	la hauteur des menus, donc ne pas mettre 0 et faire "� t�ton". en px */
var bb_top_menu_to_ssmenu = 23;
var top_ssmenu = top_menu + bb_top_menu_to_ssmenu;

/* Position gauche du menu, en px. */
var left_menu = 10;
/* En version verticale.
	Position des sous-menus par rapport au bord gauche de l'�cran. */
var bb_right_menu_to_ssmenu = 10;
var left_ssmenu = left_menu + largeur_menu + bb_right_menu_to_ssmenu;

/* Quand la souris quitte un sous-menu, si le sous-menu disparait imm�diatement,
	cela g�ne l'utilisateur. Alors on peut mettre un d�lai avant disparition du sous-menu.
	500 ms c'est bien :-) */
var delai = 650; // en milliseconde

/* En version horizontale.
	Comme le menu peut se superposer avec le texte de la page, il est possible de faire
	descendre un peu la page (on augmente la marge du haut) pour a�rer un peu la page,
	une quarantaine de pixel c'est pas mal. en px*/
var bbAddMarginsToDoc = false;
var marge_en_haut_de_page = top_menu + 40;
/* En version verticale.
	On d�cale le document � droite pour pas que le menu le superpose. */
var marge_a_gauche_de_la_page = largeur_menu + 10;


/* Mettez � true si vous souhaitez que le menu soit toujours visible.
	Mettez false si vous ne le souhaitez pas, dans ce cas le menu "dispara�tra" quand vous
	descendrez dans la page. */
var suivre_le_scroll=false;

/* Pour IE uniquement, les balises <select> passent toujours au-dessus du menu, donc
	par d�faut on cache les listes d�roulantes quand le menu est ouvert, puis on les fait
	r�appara�tre � la fermeture du menu. Pour emp�cher �a, mettre � false. */
var cacher_les_select=true;


var timeout; //ne pas toucher, c'est pour d�clarer la variable
var agt = navigator.userAgent.toLowerCase();
var isMac = (agt.indexOf('mac') != -1);
var isOpera = (agt.indexOf("opera") != -1);
var IEver = parseInt(agt.substring(agt.indexOf('msie ') + 5),10);
var isIE = ((agt.indexOf('msie')!=-1 && !isOpera && (agt.indexOf('webtv')==-1)) && !isMac);
var isIE5win = (isIE && IEver == 5);
var isIE5mac = ((agt.indexOf("msie") != -1) && isMac);
var blnOk=true;
//pour enlever les "px" pour faire des calculs...
var reg = new RegExp("px", "g");

// onScroll pour Internet Explorer, le position:fixed fait ce boulot pour les autres navigateurs
// qui respectent les normes CSS...
window.onscroll = function()
{
	if (blnOk && suivre_le_scroll && (isIE || isIE5mac))
	{
		if (isIE5mac) document.getElementById("conteneurmenu").style.visibility="hidden";
		var cumul=0;
		for(i=1;i<=nbmenu;i++)
		{
			if (!vertical) {
				document.getElementById("menu"+i).style.top = document.body.scrollTop + top_menu + "px";
				if (document.getElementById("ssmenu"+i))//undefined
					document.getElementById("ssmenu"+i).style.top = document.body.scrollTop + top_ssmenu + "px";
			} else {
				document.getElementById("menu"+i).style.top = document.body.scrollTop
							+(((i-1)*espace_entre_menus)+cumul+1+top_menu)+"px";
				if (document.getElementById("ssmenu"+i))//undefined
					document.getElementById("ssmenu"+i).style.top = document.body.scrollTop
							+(((i-1)*espace_entre_menus)+cumul+1+top_menu)+"px";
				cumul += isFinite(hauteur_menu)?hauteur_menu:hauteur_menu[i-1];
			}
		}
		if (isIE5mac) document.getElementById("conteneurmenu").style.visibility="visible";
	}
};

function preChargement()
{
	if (document.getElementById("conteneurmenu"))
	{
		document.getElementById("conteneurmenu").style.visibility="hidden";
		//IE5 mac a un bug : quand un texte est dans un �l�ment de style float, il n'apparait pas.
		/*if (isIE5mac)
		{
			document.getElementById("conteneurmenu").style="";
		}*/
	}
}

function Chargement() {

	if (cbMenuAnchorReference !== "1") bbsetMenuLocation(document);
	
	if (!blnOk) {
		if(document.body.style.backgroundColor!="") { blnOk=false; }
		if(document.body.style.color!="") { blnOk=false; }
		if(document.body.style.marginTop!="") { blnOk=false; }
		if(document.getElementById) {
			with(document.getElementById("conteneurmenu").style) {
				if(position!="" || top!="" || left!=""
						|| width!="" || height!="" || zIndex!=""
						|| margin!="" || visibility!="") {
					blnOk=false;
					alert("BB:ko1");
				}
			}
		}
		else{
			blnOk=false;
		}
	}

	if(blnOk)
	{
		document.getElementById("conteneurmenu").style.visibility="hidden";
		
		trimespaces();
		
		if (bbAddMarginsToDoc) {
			with(document.body.style) {
				if (!vertical) marginTop=marge_en_haut_de_page+"px";
				else		   marginLeft=marge_a_gauche_de_la_page+"px";
			}
		}
		
		positionne();
		CacherMenus();
	}

	// comme on a �vit� le clignotement, maintenant on fait appara�tre le menu ;-)
	document.getElementById("conteneurmenu").style.visibility='';
}
window.onresize = Chargement;

/*
 * Place les �l�ments du menu correctement, au chargement, au scroll, au redimensionnement
 * de la fen�tre
 */
function positionne() {
	var largeur_fenetre = (isIE?document.body.clientWidth:window.innerWidth);
	var hauteur_fenetre = (isIE?document.body.clientHeight:window.innerHeight);
	if (centrer_menu) {
		if (!vertical) {
			var largeur_totale = espace_entre_menus * (nbmenu-1);
			if (isFinite(largeur_menu))
				largeur_totale += largeur_menu * nbmenu;
			else {
				for (i = 1; i <= nbmenu; i++)
					largeur_totale += largeur_menu[i-1];
			}
			left_menu = (largeur_fenetre - largeur_totale)/2;
		} else {
			var hauteur_totale = espace_entre_menus * (nbmenu-1);
			if (isFinite(hauteur_menu))
				hauteur_totale += hauteur_menu * nbmenu;
			else {
				for (i = 1; i <= nbmenu; i++)
					hauteur_totale += hauteur_menu[i-1];
			}
			top_menu = (hauteur_fenetre - hauteur_totale)/2;
		}
	}
	
	//Menus
	var i, cumul = 0;
	//non: top_menu = document.getElementById("menu"+1).style.top;
	//non: left_menu = document.getElementById("menu"+1).style.left;
	if (cbMenuAnchorReference !== "1") {
		for(i=1;i<=nbmenu;i++) {
			with(document.getElementById("menu"+i).style) {
				if (!vertical) {
					top=top_menu+"px";
					//left=(((i-1)*(largeur_menu+espace_entre_menus))+1+left_menu)+"px";
					//left=(((i-1)*espace_entre_menus)+cumul+1+left_menu)+"px";
					left=(((i-1)*espace_entre_menus)+cumul+left_menu)+"px";
				} else {
					//top=(((i-1)*(hauteur_menu+espace_entre_menus))+1+top_menu)+"px";
					//top=(((i-1)*espace_entre_menus)+cumul+1+top_menu)+"px";
					top=(((i-1)*espace_entre_menus)+cumul+top_menu)+"px";
					left=left_menu+"px";
				}
				if (!suivre_le_scroll || isIE || isIE5mac)
					position="absolute";
				else position="fixed";
				//if (vertical) height=hauteur_menu+"px";
				margin="0";
				zIndex="2";
				if (vertical || isFinite(largeur_menu))
					width=largeur_menu+"px";
				else
					width=largeur_menu[i-1]+"px";
				if ((!vertical && isFinite(largeur_menu)) || (vertical && isFinite(hauteur_menu))) {
					cumul += (!vertical?largeur_menu:hauteur_menu);
				}
				else {
					cumul += (!vertical?largeur_menu[i-1]:hauteur_menu[i-1]);
					if (vertical) height=hauteur_menu[i-1]+"px";
				}
			}
		}
	}
	
	//Sous-menus
	cumul = 0;
	for(i=1;i<=nbmenu;i++) {
		if (document.getElementById("ssmenu"+i))//undefined
		{
			with(document.getElementById("ssmenu"+i).style) {
				if (cbMenuAnchorReference !== "1") {
					if (!suivre_le_scroll || isIE || isIE5mac)
						position="absolute";
					else position="fixed";
					if (!vertical) {
						top=top_ssmenu+"px";
						//left=(((i-1)*(largeur_menu+espace_entre_menus))+1+left_menu)+"px";
						left=(((i-1)*espace_entre_menus)+cumul+1+left_menu)+"px";
					} else {
						left=left_ssmenu+"px";
						//top=(((i-1)*(hauteur_menu+espace_entre_menus))+1+top_menu)+"px";
						top=(((i-1)*espace_entre_menus)+cumul+1+top_menu)+"px";
					}
				}
				if ( /*//BB added no_wrap to CSS solved problem isIE || isOpera || */ isIE5mac || !largeur_auto_ssmenu) {
					if (isFinite(largeur_sous_menu)) {
						width = largeur_sous_menu+(largeur_sous_menu!="auto"?"px":"");
					} else {
						width = largeur_sous_menu[i-1]+(largeur_sous_menu[i-1]!="auto"?"px":"");
					}
				}
				else if (cbMenuAnchorReference !== "1") width = "auto";
				if (cbMenuAnchorReference !== "1") {
					if (!vertical && !isIE5mac) {
						//repositionnement si d�borde � droite
						if ((width != "auto")
						&& ((left.replace(reg,'').valueOf()*1 + width.replace(reg,'').valueOf()*1) > largeur_fenetre))
						left = (largeur_fenetre-width.replace(reg,'').valueOf())+"px";
					}
					margin="0";
					zIndex="3";
				}
			}
		}
		if (cbMenuAnchorReference !== "1") {
			if ((!vertical && isFinite(largeur_menu)) || (vertical && isFinite(hauteur_menu))) {
				cumul += (!vertical?largeur_menu:hauteur_menu);
			}
			else {
				cumul += (!vertical?largeur_menu[i-1]:hauteur_menu[i-1]);
			}
		}
	}
}


function MontrerMenu(strMenu) {
	if(blnOk) {
		AnnulerCacher();
		CacherMenus();
		if (document.getElementById(strMenu))//undefined
			with (document.getElementById(strMenu).style)
				visibility="visible";
	}
	SelectVisible("hidden",document.getElementsByTagName('select'));
}

function CacherDelai() {
	if (blnOk) {
		timeout = setTimeout('CacherMenus()',delai);
	}
}
function AnnulerCacher() {
	if (blnOk && timeout) {
		clearTimeout(timeout);
	}
}
function CacherMenus() {
	if(blnOk) {
		for(i=1;i<=nbmenu;i++) {
			if (document.getElementById("ssmenu"+i))//undefined
				with(document.getElementById("ssmenu"+i).style)
					visibility="hidden";
		}
	}
	SelectVisible("visible",document.getElementsByTagName('select'));
}

function trimespaces() {
	//Contourne un bug d'IE5/win... il ne capte pas bien les css pour les <li>, donc on les vire !
	if(blnOk&&isIE5win) {
		for(i=1;i<=nbmenu;i++) {
			if (document.getElementById("ssmenu"+i))//undefined
				with(document.getElementById("ssmenu"+i))
					innerHTML = innerHTML.replace(/<LI>|<\/LI>/g,"");
		}
	}
}

function SelectVisible(v,elem) {
	if (blnOk && cacher_les_select && (isIE||isIE5win))
		for (var i=0;i<elem.length;i++) elem[i].style.visibility=v;
}


//Beat Added:

function bbsetMenuLocation(ref) {
	var menu_top_left;
	if (cbMenuAnchorReference == "1"){
		cbMenuAnchorReference = "conteneurmenu";
	}
	menu_top_left = bbgetLocation(cbMenuAnchorReference, ref);
	if (menu_top_left) {
		// alert(menu_top_left);
		top_menu = menu_top_left[1];
		left_menu = menu_top_left[0];
		top_ssmenu = top_menu + bb_top_menu_to_ssmenu;
		left_ssmenu = left_menu + largeur_menu + bb_right_menu_to_ssmenu;
	}
}

function bbgetLocation(anchorReference, ref) {
	var markedObjLocation;
	var markedObj = bbgetRefById(anchorReference, ref.document);
	if (!markedObj) {
		alert('WARNING: menu anchor id="'+anchorReference+'" not found in html.');
		return null;
	} else {
		markedObjLocation = bbgetAnchorLocation(markedObj);
	}
	return markedObjLocation;
}

// Adapted to overlib from jwin by Jason Anderson -- http://www.jwinlib.com
function bbgetAnchorLocation(objRef){
	var mkObj, of, offsets, mlyr;
	
	mkObj = mlyr = objRef;
	offsets = [cbMenuAnchorOffsetx, cbMenuAnchorOffsety];
	
	if (document.layers){
		if (typeof mlyr.length != 'undefined' &&  mlyr.length > 1) {
				mkObj = mlyr[0];
				offsets[0] += mlyr[0].x + mlyr[1].pageX;
				offsets[1] += mlyr[0].y + mlyr[1].pageY;
			} else {
				if(mlyr.toString().indexOf('Image') != -1 || mlyr.toString().indexOf('Anchor') != -1){
					offsets[0] += mlyr.x;
					offsets[1] += mlyr.y;
				} else {
					offsets[0] += mlyr.pageX;
					offsets[1] += mlyr.pageY;
				}
			}          
	} else {
		offsets[0] += bbpageLocation(mlyr, 'Left');
		offsets[1] += bbpageLocation(mlyr, 'Top');
	}
/*
	of = getAnchorOffsets(mkObj);
	offsets[0] += of[0];          
	offsets[1] += of[1];
*/
	return offsets;
}

// Adapted to overlib from jwin by Jason Anderson -- http://www.jwinlib.com
function bbpageLocation(o, t){
	var x = 0;

	while(o){
		//	alert(t+": "+o.nodeName+": "+ o['offset' + t]);
		x += o['offset' + t];
		o = o.offsetParent;
	}
	//BB if ((o.offsetParent) && o.offsetParent.nodeName == 'BODY') x += o.offsetParent['offset' + t];		//BB: try fixing FF&Safari, not IE&Opera

	return x;
} 

// Adapted to overlib from jwin by Jason Anderson -- http://www.jwinlib.com
function bbgetRefById(l, d){
	var r = "", j;

	d = (d || document);
	if (d.all) {
		return d.all[l];
	} else if (d.getElementById) {
		return d.getElementById(l);
	} else if (d.layers &&  d.layers.length > 0) {
		if (d.layers[l]) {
			return d.layers[l];
		}
		for (j=0; j < d.layers.length; j++) {
			r = getRefById(l, d.layers[j].document);
			if (r) {
				return r;
			}
		}
	}

	return false;
}

// Adapted to overlib from jwin by Jason Anderson -- http://www.jwinlib.com
function bbgetRefByName(l, d) {
	var r = null, j;

	d = (d || document);

	if (d.images[l]) {
		return d.images[l];
	} else if (d.anchors[l]) {
		return d.anchors[l];
	} else if (d.layers &&  d.layers.length > 0) {
		for (j=0; j < d.layers.length; j++) {
			r = getRefByName(l, d.layers[j].document);
			if (r &&  r.length > 0) {
				return r;
			} else if (r) {
				return [r, d.layers[j]];
			}
		}
	}

	return null;
}

