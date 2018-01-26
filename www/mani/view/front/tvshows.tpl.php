<?php
if (!defined("_MANI"))
    die('Direct access to this location is not allowed.');
?>
<div class="content-center"><img src="<?php echo UPLOADURL;?>/avatars/<?php echo (App::Auth()->avatar) ? App::Auth()->avatar : "blank.png";?>" alt="" class="avatar"></div>

<div class="content-center"><img class="subscribe-banner" src="/mani/view/front/images/tvshows_icon.png" alt="" class="header_icon"></div>

<?php if($this->data):?>
    <div style="">
        <table id="showListTableShows" class="tablesorter tablesorter-default tablesorter236e0314cb45fcolumnselector hasSaveSort hasFilters hasStickyHeaders" cellspacing="1" border="0" cellpadding="0" role="grid">
            <thead>
                <tr role="row" class="tablesorter-headerRow">
                    <th data-column="0" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" style="user-select: none;"><div class="tablesorter-header-inner">First Aired</div></th>
                    <th data-column="1" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on style="user-select: none;"><div class="tablesorter-header-inner">Classification</div></th>
                    <th data-column="2" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" style="user-select: none;"><div class="tablesorter-header-inner">Show</div></th>
                    <th data-column="3" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" style="user-select: none;"><div class="tablesorter-header-inner">Network</div></th>
                    <th data-column="4" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" astyle="user-select: none;"><div class="tablesorter-header-inner">Status</div></th>
                    <th data-column="5" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" style="user-select: none;"><div class="tablesorter-header-inner">Rating</div></th>
                    <th data-column="5" class="nowrap tablesorter-header tablesorter-headerUnSorted" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="showListTableShows" unselectable="on" style="user-select: none;"><div class="tablesorter-header-inner">Subscribe</div></th>
                </tr>
                <tr role="row" class="tablesorter-filter-row tablesorter-ignoreRow hideme">
                    <td data-column="0"><input type="search" placeholder="" class="tablesorter-filter" data-column="0"></td>
                    <td data-column="1"><input type="search" placeholder="" class="tablesorter-filter" data-column="1"></td>
                    <td data-column="2"><input type="search" placeholder="" class="tablesorter-filter" data-column="2"></td>
                    <td data-column="3"><input type="search" placeholder="" class="tablesorter-filter" data-column="3"></td>
                    <td data-column="4"><input type="search" placeholder="" class="tablesorter-filter" data-column="4"></td>
                    <td data-column="5"><input type="search" placeholder="" class="tablesorter-filter" data-column="5"></td>
                    <td data-column="6"><input type="search" placeholder="" class="tablesorter-filter" data-column="6"></td>
                </tr>
            </thead>
            <tfoot class="hidden-print">
                <tr role="row">
                    <th data-column="1">&nbsp;</th>
                    <th data-column="1">&nbsp;</th>
                    <th data-column="2">&nbsp;</th>
                    <th data-column="3">&nbsp;</th>
                    <th data-column="4">&nbsp;</th>
                    <th data-column="5">&nbsp;</th>
                    <th data-column="6">&nbsp;</th>
                </tr>
            </tfoot>
            <tbody>
            <?php foreach ($this->data["available"] as $locations):?>
                <?php foreach ($locations["content"] as $mrow):?>
                <tr role="row" class="odd">

                    <!-- First Aired -->
                    <td align="center">
                        <time datetime="2017-05-25T03:00:00+02:00" class="date"><?php echo $mrow["premiered"];?></time>
                    </td>

                    <!-- Classification -->
                    <td align="center" class="nowrap">
                        <?php echo $mrow["mpaa"];?>
                    </td>

                    <!-- Banner/Title -->
                    <td>
                        <span style="display: none;"><?php echo $mrow["title"];?></span>
                        <div class="imgbanner banner">
                            <a href="http://dereferer.org/?http://thetvdb.com/?tab=series&id=<?php echo $mrow["tvdbid"];?>" target="_blank">
                                <img src="/mani/ajax/?do=image&loc=<?php echo $locations["title"];?>&type=<?php echo $mrow["type"];?>&media=<?php echo $mrow["media_banner"];?>"  class="banner" alt="<?php echo $mrow["title"];?>" title="<?php echo $mrow["title"];?>">
                            </a>
                        </div>
                    </td>

                    <!-- Network -->
                    <td align="center">
                        <span title="<?php echo $mrow["studio"];?>" class="hidden-print"><img id="network" width="54" height="27" src="/mani/ajax/?do=image&type=network&media=<?php echo rawurlencode(strtolower($mrow["studio"]));?>.png" alt="<?php echo $mrow["studio"];?>" title="<?php echo $mrow["studio"];?>"></span>
                    </td>

                    <!-- Status -->
                    <td align="center">
                        <?php echo $mrow["status"];?>
                    </td>

                    <!-- Rating -->
                    <td align="center">
                        <?php echo $mrow["rating"];?>
                    </td>

                    <!-- Subscribe Toggle -->
                    <td align="center">
                        <input type="checkbox" name="media_enabled_<?php echo $mrow["id"];?>" id="media_enabled_<?php echo $mrow["id"];?>" <?php echo $mrow["enabled"];?> onclick="toggleShowSelection('<?php echo $mrow["id"];?>');">
                        <input type="hidden" name="media_type_<?php echo $mrow["id"];?>" id="media_type_<?php echo $mrow["id"];?>" value="<?php echo $mrow["type"];?>" >
                    </td>
                </tr>
                <?php endforeach;?>
            <?php endforeach;?>
            </tbody>
        </table>
    </div>
<?php endif;?>