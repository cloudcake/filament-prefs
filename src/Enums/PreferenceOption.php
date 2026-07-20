<?php

namespace Cloudcake\Prefs\Enums;

enum PreferenceOption
{
    case Timezone;
    case DateFormat;
    case TimeFormat;
    case TablePersistFilters;
    case TablePersistSearch;
    case TablePersistColumns;
    case TableStripedRows;
    case DialogDisplayMode;
    case DialogCloseEscape;
    case DialogCloseOutside;
    case SectionsCompact;
    case SectionsCollapsible;
    case SectionsPersistCollapse;
    case NotificationHorizontalPosition;
    case NotificationVerticalPosition;
    case NotificationDuration;
}