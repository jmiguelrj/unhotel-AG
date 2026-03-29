// src/utils/icons.js
import React from 'react';
import {
    Search,
    ChevronRight,
    User,
    MessageSquare,
    CheckCircle2,
    FileText,
    MoreHorizontal,
    Phone,
    Mail,
    LogIn,
    LogOut,
    AlertCircle,
    Check,
    Copy,
    RefreshCw,
    ArrowUp,
    ArrowDown,
    X,
    Zap, // Last-minute icon
    Plane, Car, Bus, Footprints, MapPin, Clock, // Logistics icons
    Users, Moon, Star, Menu // UI Enhancements icons
} from 'lucide-react';

export const Icons = {
    Search, ChevronRight, User, MessageSquare, CheckCircle2, FileText,
    MoreHorizontal, Phone, Mail, LogIn, LogOut, AlertCircle, Check,
    Copy, RefreshCw, ArrowUp, ArrowDown, X, Zap,
    Plane, Car, Bus, Footprints, MapPin, Clock,
    Users, Moon, Star, Menu
};

// Ensure all icons are defined.
Object.keys(Icons).forEach(key => {
    if (!Icons[key]) {
        console.error(`FrontDesk Critical Error: Icon "${key}" is undefined. Check lucide-react version.`);
    }
});

export const SafeIcon = ({ icon: IconComponent, ...props }) => {
    if (!IconComponent) return <span style={{ color: 'red', fontSize: '10px' }}>?</span>;
    return <IconComponent {...props} />;
};
