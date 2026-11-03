/*
 * Icônes de l'interface (Heroicons). Les pages gardent des noms d'usage et les
 * réglages « size » et « weight » : weight="fill" donne la version pleine, tout
 * le reste la version contour (« bold » épaissit le trait). Changer de jeu
 * d'icônes ne touche que ce fichier.
 */
import {
    ArrowDownTrayIcon as ArrowDownTrayOutline,
    ArrowLeftIcon as ArrowLeftOutline,
    ArrowRightIcon as ArrowRightOutline,
    ArrowRightStartOnRectangleIcon as ArrowRightStartOnRectangleOutline,
    BuildingOfficeIcon as BuildingOfficeOutline,
    ChatBubbleLeftEllipsisIcon as ChatBubbleLeftEllipsisOutline,
    CheckIcon as CheckOutline,
    CheckCircleIcon as CheckCircleOutline,
    ChevronRightIcon as ChevronRightOutline,
    ClipboardDocumentListIcon as ClipboardDocumentListOutline,
    ClockIcon as ClockOutline,
    DocumentDuplicateIcon as DocumentDuplicateOutline,
    DocumentTextIcon as DocumentTextOutline,
    ExclamationCircleIcon as ExclamationCircleOutline,
    ExclamationTriangleIcon as ExclamationTriangleOutline,
    EyeIcon as EyeOutline,
    EyeSlashIcon as EyeSlashOutline,
    FolderIcon as FolderOutline,
    FolderOpenIcon as FolderOpenOutline,
    FunnelIcon as FunnelOutline,
    GlobeEuropeAfricaIcon as GlobeEuropeAfricaOutline,
    IdentificationIcon as IdentificationOutline,
    InboxIcon as InboxOutline,
    InformationCircleIcon as InformationCircleOutline,
    KeyIcon as KeyOutline,
    LockClosedIcon as LockClosedOutline,
    MagnifyingGlassIcon as MagnifyingGlassOutline,
    PaperAirplaneIcon as PaperAirplaneOutline,
    PaperClipIcon as PaperClipOutline,
    PencilIcon as PencilOutline,
    PencilSquareIcon as PencilSquareOutline,
    PhoneIcon as PhoneOutline,
    PlusIcon as PlusOutline,
    ShieldCheckIcon as ShieldCheckOutline,
    Squares2X2Icon as Squares2X2Outline,
    TrashIcon as TrashOutline,
    UserIcon as UserOutline,
    UserCircleIcon as UserCircleOutline,
    UserGroupIcon as UserGroupOutline,
    XMarkIcon as XMarkOutline,
    MoonIcon as MoonOutline,
    SunIcon as SunOutline,
} from '@heroicons/react/24/outline';
import {
    ArrowDownTrayIcon as ArrowDownTraySolid,
    ArrowLeftIcon as ArrowLeftSolid,
    ArrowRightIcon as ArrowRightSolid,
    ArrowRightStartOnRectangleIcon as ArrowRightStartOnRectangleSolid,
    BuildingOfficeIcon as BuildingOfficeSolid,
    ChatBubbleLeftEllipsisIcon as ChatBubbleLeftEllipsisSolid,
    CheckIcon as CheckSolid,
    CheckCircleIcon as CheckCircleSolid,
    ChevronRightIcon as ChevronRightSolid,
    ClipboardDocumentListIcon as ClipboardDocumentListSolid,
    ClockIcon as ClockSolid,
    DocumentDuplicateIcon as DocumentDuplicateSolid,
    DocumentTextIcon as DocumentTextSolid,
    ExclamationCircleIcon as ExclamationCircleSolid,
    ExclamationTriangleIcon as ExclamationTriangleSolid,
    EyeIcon as EyeSolid,
    EyeSlashIcon as EyeSlashSolid,
    FolderIcon as FolderSolid,
    FolderOpenIcon as FolderOpenSolid,
    FunnelIcon as FunnelSolid,
    GlobeEuropeAfricaIcon as GlobeEuropeAfricaSolid,
    IdentificationIcon as IdentificationSolid,
    InboxIcon as InboxSolid,
    InformationCircleIcon as InformationCircleSolid,
    KeyIcon as KeySolid,
    LockClosedIcon as LockClosedSolid,
    MagnifyingGlassIcon as MagnifyingGlassSolid,
    PaperAirplaneIcon as PaperAirplaneSolid,
    PaperClipIcon as PaperClipSolid,
    PencilIcon as PencilSolid,
    PencilSquareIcon as PencilSquareSolid,
    PhoneIcon as PhoneSolid,
    PlusIcon as PlusSolid,
    ShieldCheckIcon as ShieldCheckSolid,
    Squares2X2Icon as Squares2X2Solid,
    TrashIcon as TrashSolid,
    UserIcon as UserSolid,
    UserCircleIcon as UserCircleSolid,
    UserGroupIcon as UserGroupSolid,
    XMarkIcon as XMarkSolid,
    MoonIcon as MoonSolid,
    SunIcon as SunSolid,
} from '@heroicons/react/24/solid';

function icon(Outline, Solid) {
    function Icon({ size = 24, weight = 'regular', ...props }) {
        const Component = weight === 'fill' ? Solid : Outline;
        const stroke = weight === 'bold' && Component === Outline ? { strokeWidth: 2 } : {};

        return <Component width={size} height={size} aria-hidden="true" focusable="false" {...stroke} {...props} />;
    }

    return Icon;
}

export const ArrowLeft = icon(ArrowLeftOutline, ArrowLeftSolid);
export const ArrowRight = icon(ArrowRightOutline, ArrowRightSolid);
export const Buildings = icon(BuildingOfficeOutline, BuildingOfficeSolid);
export const CaretRight = icon(ChevronRightOutline, ChevronRightSolid);
export const ChatCircleDots = icon(ChatBubbleLeftEllipsisOutline, ChatBubbleLeftEllipsisSolid);
export const Check = icon(CheckOutline, CheckSolid);
export const CheckCircle = icon(CheckCircleOutline, CheckCircleSolid);
export const Clock = icon(ClockOutline, ClockSolid);
export const Copy = icon(DocumentDuplicateOutline, DocumentDuplicateSolid);
export const Detective = icon(EyeSlashOutline, EyeSlashSolid);
export const DoorOpen = icon(ArrowRightStartOnRectangleOutline, ArrowRightStartOnRectangleSolid);
export const DownloadSimple = icon(ArrowDownTrayOutline, ArrowDownTraySolid);
export const Eye = icon(EyeOutline, EyeSolid);
export const EyeSlash = icon(EyeSlashOutline, EyeSlashSolid);
export const FileText = icon(DocumentTextOutline, DocumentTextSolid);
export const FolderOpen = icon(FolderOpenOutline, FolderOpenSolid);
export const Folders = icon(FolderOutline, FolderSolid);
export const FunnelSimple = icon(FunnelOutline, FunnelSolid);
export const Globe = icon(GlobeEuropeAfricaOutline, GlobeEuropeAfricaSolid);
export const Hourglass = icon(ClockOutline, ClockSolid);
export const Info = icon(InformationCircleOutline, InformationCircleSolid);
export const Key = icon(KeyOutline, KeySolid);
export const ListChecks = icon(ClipboardDocumentListOutline, ClipboardDocumentListSolid);
export const LockKey = icon(LockClosedOutline, LockClosedSolid);
export const Moon = icon(MoonOutline, MoonSolid);
export const MagnifyingGlass = icon(MagnifyingGlassOutline, MagnifyingGlassSolid);
export const NotePencil = icon(PencilSquareOutline, PencilSquareSolid);
export const PaperPlaneRight = icon(PaperAirplaneOutline, PaperAirplaneSolid);
export const Paperclip = icon(PaperClipOutline, PaperClipSolid);
export const PencilSimple = icon(PencilOutline, PencilSolid);
export const Phone = icon(PhoneOutline, PhoneSolid);
export const Plus = icon(PlusOutline, PlusSolid);
export const ShieldCheck = icon(ShieldCheckOutline, ShieldCheckSolid);
export const SignOut = icon(ArrowRightStartOnRectangleOutline, ArrowRightStartOnRectangleSolid);
export const SquaresFour = icon(Squares2X2Outline, Squares2X2Solid);
export const Sun = icon(SunOutline, SunSolid);
export const Trash = icon(TrashOutline, TrashSolid);
export const Tray = icon(InboxOutline, InboxSolid);
export const User = icon(UserOutline, UserSolid);
export const UserCircle = icon(UserCircleOutline, UserCircleSolid);
export const UserCircleDashed = icon(IdentificationOutline, IdentificationSolid);
export const UsersThree = icon(UserGroupOutline, UserGroupSolid);
export const Warning = icon(ExclamationTriangleOutline, ExclamationTriangleSolid);
export const WarningCircle = icon(ExclamationCircleOutline, ExclamationCircleSolid);
export const X = icon(XMarkOutline, XMarkSolid);
