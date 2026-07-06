export const metadata = {
  title: 'Наличники',
}

export default () => null

export const getServerSideProps = async () => {
  return {
    redirect: {
      destination: '/nalichniki',
      permanent: true,
    },
  }
}
