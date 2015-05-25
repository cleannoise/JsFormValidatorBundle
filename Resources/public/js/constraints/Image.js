function SymfonyComponentValidatorConstraintsImage() {
    this.mimeTypes = 'image/*';

    this.checkMimeType = function (mimeType) {
        return this.strstr(mimeType, this.strstr(this.mimeTypes, '/*', true), false) === false;
    };

    this.strstr = function (haystack, needle, bool) {
        var pos = 0;
        haystack += '';
        pos = haystack.indexOf(needle);
        if (pos == -1) {
            return false;
        } else {
            if (bool) {
                return haystack.substr(0, pos);
            } else {
                return haystack.slice(pos);
            }
        }
    }
    this.onCreate = function () {

    }
}
SymfonyComponentValidatorConstraintsImage.prototype = new SymfonyComponentValidatorConstraintsFile;