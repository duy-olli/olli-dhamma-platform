<template>
  <p>Logging off...</p>
</template>

<script>
import { unsetToken } from '~/utils/auth'

export default {
  mounted() {
    const self = this

    unsetToken()
    setInterval(function() {
      self.$router.push('/admin/login')
    }, 2000)
  }
}
</script>
