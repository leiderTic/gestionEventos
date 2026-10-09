/**
 * useCountryCodes
 * Static list of country calling codes (ITU-T E.164).
 * Bolivia (+591) is the first entry so it is the default.
 */
export function useCountryCodes() {
    const countryCodes = [
        { code: '591', label: '🇧🇴 Bolivia (+591)',       flag: '🇧🇴', name: 'Bolivia' },
        { code: '54',  label: '🇦🇷 Argentina (+54)',      flag: '🇦🇷', name: 'Argentina' },
        { code: '55',  label: '🇧🇷 Brasil (+55)',          flag: '🇧🇷', name: 'Brasil' },
        { code: '56',  label: '🇨🇱 Chile (+56)',           flag: '🇨🇱', name: 'Chile' },
        { code: '57',  label: '🇨🇴 Colombia (+57)',        flag: '🇨🇴', name: 'Colombia' },
        { code: '593', label: '🇪🇨 Ecuador (+593)',        flag: '🇪🇨', name: 'Ecuador' },
        { code: '595', label: '🇵🇾 Paraguay (+595)',       flag: '🇵🇾', name: 'Paraguay' },
        { code: '51',  label: '🇵🇪 Perú (+51)',            flag: '🇵🇪', name: 'Perú' },
        { code: '598', label: '🇺🇾 Uruguay (+598)',        flag: '🇺🇾', name: 'Uruguay' },
        { code: '58',  label: '🇻🇪 Venezuela (+58)',       flag: '🇻🇪', name: 'Venezuela' },
        { code: '52',  label: '🇲🇽 México (+52)',          flag: '🇲🇽', name: 'México' },
        { code: '1',   label: '🇺🇸 EE.UU. / Canadá (+1)',  flag: '🇺🇸', name: 'EE.UU.' },
        { code: '34',  label: '🇪🇸 España (+34)',          flag: '🇪🇸', name: 'España' },
        { code: '44',  label: '🇬🇧 Reino Unido (+44)',     flag: '🇬🇧', name: 'Reino Unido' },
        { code: '49',  label: '🇩🇪 Alemania (+49)',        flag: '🇩🇪', name: 'Alemania' },
        { code: '33',  label: '🇫🇷 Francia (+33)',         flag: '🇫🇷', name: 'Francia' },
        { code: '39',  label: '🇮🇹 Italia (+39)',          flag: '🇮🇹', name: 'Italia' },
        { code: '351', label: '🇵🇹 Portugal (+351)',       flag: '🇵🇹', name: 'Portugal' },
        { code: '7',   label: '🇷🇺 Rusia (+7)',            flag: '🇷🇺', name: 'Rusia' },
        { code: '86',  label: '🇨🇳 China (+86)',           flag: '🇨🇳', name: 'China' },
        { code: '81',  label: '🇯🇵 Japón (+81)',           flag: '🇯🇵', name: 'Japón' },
        { code: '91',  label: '🇮🇳 India (+91)',           flag: '🇮🇳', name: 'India' },
        { code: '971', label: '🇦🇪 Emiratos Árabes (+971)', flag: '🇦🇪', name: 'EAU' },
        { code: '27',  label: '🇿🇦 Sudáfrica (+27)',      flag: '🇿🇦', name: 'Sudáfrica' },
        { code: '20',  label: '🇪🇬 Egipto (+20)',          flag: '🇪🇬', name: 'Egipto' },
        { code: '61',  label: '🇦🇺 Australia (+61)',       flag: '🇦🇺', name: 'Australia' },
    ];

    const defaultCode = '591'; // Bolivia

    /**
     * Builds the WhatsApp URL for a given phone code + number.
     * @param {string} code  - Country calling code (e.g. "591")
     * @param {string} phone - Local phone number (digits only)
     * @returns {string} WhatsApp URL
     */
    const buildWhatsAppUrl = (code, phone) => {
        const digits = String(phone || '').replace(/\D/g, '');
        return `https://wa.me/${code}${digits}`;
    };

    return { countryCodes, defaultCode, buildWhatsAppUrl };
}
