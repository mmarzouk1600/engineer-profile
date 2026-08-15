export default {
    name: "Permissions",
    methods: {
        can: function (permissionOrPermissions) {
            let permissions = [].concat(permissionOrPermissions)
            let allPermissions = this.$page.props.auth.can;

            let user = this.$page.props.auth.user;
            let hasPermission = false;
            permissions.forEach(function(item){
                if(allPermissions[item]) hasPermission = true;
            });

            // if user has super admin role
            if (user && user.type == 1){
                return  true;
            }
            return hasPermission;
        },
        hasRole: function (roleOrRoles) {
            let roles = [].concat(roleOrRoles)
            let allRoles = this.$page.props.auth.roles;
            let hasRole = false;
            roles.forEach(function(role){
                if(allRoles.includes(role)) hasRole = true;
            });
            return hasRole;
        },
    }
}
